<?php
namespace App\Console\Commands;

use App\User;
use App\Contact;
use App\Country;
use Sabre\VObject\Reader;
use Illuminate\Console\Command;
use Sabre\VObject\Component\VCard;
use Illuminate\Filesystem\Filesystem;

class ImportVCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:vcard {user} {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports contacts from vCard files for a specific user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Filesystem $filesystem
     * @return mixed
     */
    public function handle(Filesystem $filesystem)
    {
        $path = './' . $this->argument('path');
        $user = User::where('email', $this->argument('user'))->first();
        if (!$user) {
            $this->error('You need to provide a valid user email!');
            return;
        }
        if (!$filesystem->exists($path) || $filesystem->extension($path) !== 'vcf') {
            $this->error('The provided vcard file was not found or is not valid!');
            return;
        }
        $matchCount = preg_match_all('/(BEGIN:VCARD.*?END:VCARD)/s', $filesystem->get($path), $matches);
        $this->info("We found {$matchCount} contacts in {$path}.");
        if ($this->confirm('Would you like to import them?', true)) {
            $this->info("Importing contacts from {$path}");
            $this->output->progressStart($matchCount);
            $skippedContacts = 0;
            collect($matches[0])->map(function ($vcard) {
                return Reader::read($vcard);
            })->each(function (VCard $vcard) use ($user, $skippedContacts) {
                if ($this->contactExists($vcard, $user)) {
                    $this->output->progressAdvance();
                    $skippedContacts++;
                    return;
                }
                // Skip contact if there isn't a first name or a nickname
                if (!$this->contactHasName($vcard)) {
                    $this->output->progressAdvance();
                    $skippedContacts++;
                    return;
                }
                $contact = new Contact();
                $contact->company_id = $user->company_id;
                if ($vcard->N && !empty($vcard->N->getParts()[1])) {
                    $contact->first_name = $this->formatValue($vcard->N->getParts()[1]);
                    $contact->middle_name = $this->formatValue($vcard->N->getParts()[2]);
                    $contact->last_name = $this->formatValue($vcard->N->getParts()[0]);
                } else {
                    $contact->first_name = $this->formatValue($vcard->NICKNAME);
                }
                $contact->gender = 'none';
                $contact->is_birthdate_approximate = 'unknown';
                if ($vcard->BDAY && !empty((string)$vcard->BDAY)) {
                    $contact->birthdate = new \DateTime((string)$vcard->BDAY);
                }
                $contact->email = $this->formatValue($vcard->EMAIL);
                $contact->phone_number = $this->formatValue($vcard->TEL);
                if ($vcard->ADR) {
                    $contact->street = $this->formatValue($vcard->ADR->getParts()[2]);
                    $contact->city = $this->formatValue($vcard->ADR->getParts()[3]);
                    $contact->province = $this->formatValue($vcard->ADR->getParts()[4]);
                    $contact->postal_code = $this->formatValue($vcard->ADR->getParts()[5]);
                    $country = Country::where('country', $vcard->ADR->getParts()[6])
                        ->orWhere('iso', strtolower($vcard->ADR->getParts()[6]))
                        ->first();
                    if ($country) {
                        $contact->country_id = $country->id;
                    }
                }
                $contact->job = $this->formatValue($vcard->ORG);
                $contact->setAvatarColor();
                $contact->save();
                $contact->logEvent('contact', $contact->id, 'create');
                $this->output->progressAdvance();
            });
            $this->output->progressFinish();
            $this->info("Successfully imported {$matchCount} contacts and skipped {$skippedContacts}.");
        }
    }

    /**
     * Formats and returns a string for the contact.
     *
     * @param null|string $value
     * @return null|string
     */
    private function formatValue($value)
    {
        return !empty((string)$value) ? (string)$value : null;
    }

    /**
     * Checks whether a contact already exists for a given account.
     *
     * @param VCard $vcard
     * @param User $user
     * @return bool
     */
    private function contactExists(VCard $vcard, User $user)
    {
        $email = (string)$vcard->EMAIL;
        $contact = Contact::where([
            ['company_id', $user->company_id],
            ['email', $email],
        ])->first();
        return $email && $contact;
    }

    /**
     * Checks whether a contact has a first name or a nickname.
     * Nickname is used as a fallback if no first name is provided.
     *
     * @param VCard $vcard
     * @return bool
     */
    public function contactHasName(VCard $vcard): bool
    {
        return !empty($vcard->N->getParts()[1]) || !empty((string)$vcard->NICKNAME);
    }
}
