<?php
/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/
use App\Helpers\RandomHelper;

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'remember_token' => str_random(10),
        'timezone' => config('app.timezone'),
        'name_order' => 'firstname_first',
        'company_id' => factory('App\Company')->create()->id,
    ];
});
$factory->define(App\Company::class, function (Faker\Generator $faker) {
    return [
        'api_key' => RandomHelper::generateString(30),
    ];
});
$factory->define(App\Activity::class, function (Faker\Generator $faker) {
    return [
        'company_id' => 1,
        'contact_id' => 1,
        'activity_type_id' => function () {
            return factory(App\ActivityType::class)->create()->id;
        },
        'description' => encrypt($faker->sentence),
        'date_it_happened' => \Carbon\Carbon::createFromTimeStamp($faker->dateTimeThisCentury()->getTimeStamp()),
    ];
});
$factory->define(App\ActivityType::class, function (Faker\Generator $faker) {
    return [
        'key' => $faker->sentence,
        'location_type' => $faker->word,
        'icon' => $faker->word,
    ];
});
$factory->define(App\Reminder::class, function (Faker\Generator $faker) {
    return [
        'company_id' => 1,
        'contact_id' => 1,
    ];
});
$factory->define(App\Contact::class, function (Faker\Generator $faker) {
    return [
        'company_id' => 1,
        'first_name' => 'John',
        'last_name' => 'Doe',
        'birthdate' => \Carbon\Carbon::createFromTimeStamp($faker->dateTimeThisCentury()->getTimeStamp()),
    ];
});
$factory->define(App\Gift::class, function (Faker\Generator $faker) {
    return [
        'created_at' => \Carbon\Carbon::createFromTimeStamp($faker->dateTimeThisCentury()->getTimeStamp()),
    ];
});
$factory->define(App\Call::class, function (Faker\Generator $faker) {
    return [
        'created_at' => \Carbon\Carbon::createFromTimeStamp($faker->dateTimeThisCentury()->getTimeStamp()),
    ];
});
$factory->define(App\Task::class, function (Faker\Generator $faker) {
    return [
        'company_id' => 1,
        'contact_id' => 1,
        'created_at' => \Carbon\Carbon::createFromTimeStamp($faker->dateTimeThisCentury()->getTimeStamp()),
    ];
});
$factory->define(App\SignificantOther::class, function (Faker\Generator $faker) {
    return [
        'company_id' => 1,
        'contact_id' => 1,
        'status' => 'active',
        'birthdate' => \Carbon\Carbon::createFromTimeStamp($faker->dateTimeThisCentury()->getTimeStamp()),
    ];
});
$factory->define(App\Note::class, function (Faker\Generator $faker) {
    return [
        'company_id' => 1,
        'body' => encrypt($faker->text(200)),
    ];
});
$factory->define(App\Kid::class, function (Faker\Generator $faker) {
    return [
        'company_id' => 1,
        'child_of_contact_id' => 1,
        'gender' => 'male',
        'first_name' => encrypt($faker->firstName),
        'is_birthdate_approximate' => 'false',
        'birthdate' => \Carbon\Carbon::createFromTimeStamp($faker->dateTimeThisCentury()->getTimeStamp()),
        'birthday_reminder_id' => 1,
        'food_preferencies' => encrypt($faker->sentence),
    ];
});
$factory->define(App\Country::class, function (Faker\Generator $faker) {
    return [
        'iso' => 'ca',
        'country' => 'Mali',
    ];
});
