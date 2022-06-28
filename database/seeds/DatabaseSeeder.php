<?php

use App\Status;
use App\User;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UsersTableSeeder::class);
        $this->createStatus();
        $this->createCustomaryFoot();
        $this->createPositions();
        $this->createCountries();
        $this->createRoles();
        $this->categories();
        $this->levels();
        $this->skills();
        $this->tools();
        $this->access_modifiers();
        $this->addUser(5);
        $this->addTrainer(1);
        $this->addTeam();
        $this->addClub('Ajax','https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcTKz4cM5n9azL4KIHzPzxUi6Tb3m0Ta3Fw8kA&usqp=CAU');
        $this->assignTeamToTrainer(91,5);
        $this->addClubTrainerPermissions();
    }

    public function addClubTrainerPermissions(){
        $permissions=[
            'sk_edit_skill_assignment',
            'sk_create_skill_assignment',
            'sk_assign_skill_assignment',
            'sk_see_assignment_videos',
            'sk_writing_comments',
            'pd_access_all_players',
            'pd_access_only_team_players',
            'pd_see_player_videos',
            'pd_writing_comments',
        ];
        foreach ($permissions as $assignment_access){
            Permission::updateOrCreate(['name'=>$assignment_access,'guard_name'=>'api'],['name'=>$assignment_access,'guard_name'=>'api']);
        }
    }
    public function addExcersiceTipsTranslations(){
        $tip = [
            [
                'en'=>'For the jumping jacks first stand upright with your legs together, arms at your sides >>> Stand straight with your legs hip-width, arms next to your body.',
                'nl'=>'Sta rechtop met je benen bij elkaar, armen naast je lichaam.'
            ],
            [
                'en'=>'After bend your knees slightly, and jump into the air, spread your legs to be about shoulder-width apart. Stretch your arms out and over your head >>> Slightly bend your knees, and jump into the air. Then simultaneously spread your legs shoulder-width apart and stretch your arms out, over your head.',
                'nl'=>'Buig daarna je knieën en spring omhoog. Met je voeten op schouderbreedte uit elkaar en armen volledig gestrekt boven het hoofd.'
            ],
            [
                'en'=>'The exercise will auto start with a short countdown after your whole body is visible for the camera >>> The exercise starts automatically with a countdown once your body is fully visible to the camera',
                'nl'=>'De oefening start automatisch met een countdown zodra je lichaam volledig zichtbaar is voor de camera.'
            ],
            [
                'en'=>'Lean your smartphone to a wall, water bottle or where it can sit steady during your exercise >>> Put your smartphone against a wall, water bottle or any other object it can steady lean on during your exercise.',
                'nl'=>'Plaats je smartphone tegen een muur, waterfles of ander voorwerp waar het tijdens de oefening stevig op kan leunen.',
            ],
            [
                'en'=>'Keep the ball up using the laces part of the feet. Make sure the chest is up and upper body is stable >>> Keep the ball up to knee-level using your shoelaces. Make sure your chest is straight, and your upper body is stable.',
                'nl'=>'Houd de bal met je wreef hoog tot kniehoogte. Doe dat met een rechte rug en stabiel bovenlichaam',
            ],
            [
                'en'=>'Tap the ball outwards with the outside of your foot. Control it back in with the inside of the same foot. Repeat with other foot. >>> Move the ball sideways with the outside of your foot, bring it back with an inside-touch of the same foot. Repeat this move with the opposite foot.',
                'nl'=>'Beweeg de bal zijwaarts met de buitenkant van de voet, breng deze terug met de binnenkant van dezelfde voet. Herhaal de move met je andere been'
            ],
            [
                'en'=>'Next tip',
                'nl'=>'Volgende tip'
            ],
        ];

        return $tip;
    }


    function addClub($name, $image){
        \Illuminate\Support\Facades\DB::table('clubs')->insert([
            'title'=>$name,
            'description'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. ',
            'image'=>$image
        ]);
    }

    function addUser($team_id){
        $faker = \Faker\Factory::create();
        $imeis = [
            862549047811115,
            862549047831873,
            862549047779858,
            862549047749802,
            862549047819076,
            862549047822690,
            862549047821841,
            862549047798627,
            862549047782555,
            862549047782555,
            862549047798718,
            862549047820926,
            862549047833499,
            862549047779577,
            862549047837532,
            862549047832475,
            862549047824944,
            862549047814283,
            862549047834208,
            862549047832764,
            862549047739787,
            862549047837268,
            862549047779494,
            862549047820637,
            862549047742708,
            862549047820686,
            862549047780542,
            862549047831667,
            862549047786440,
            862549047818888,
        ];
        foreach ($imeis as $imei){
            $user = new User();
            $user->first_name = $faker->firstNameMale;
            $user->last_name =$faker->lastName;
            $user->phone =$faker->e164PhoneNumber;
            $user->nationality_id =3;
            $user->profile_picture = 'media/users/placeholder.png';
            $user->date_of_birth = $faker->dateTime();
            $user->verification_code = rand(2000, 10000);
            $user->verified_at = now();
            $user->save();
            $role = Role::where('name', 'player')->first();
            $user->assignRole($role);
            \Illuminate\Support\Facades\DB::table('user_sensors')->insert([
                'user_id'=>$user->id,
                'imei'=>$imei,
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now(),
            ]);
            \Illuminate\Support\Facades\DB::table('player_team')->insert([
                'user_id'=>$user->id,
                'team_id'=>$team_id,
                'created_at'=>\Carbon\Carbon::now(),
                'updated_at'=>\Carbon\Carbon::now(),
            ]);


        }
    }


    public function assignTeamToTrainer($trainer_i,$team_id){
        \Illuminate\Support\Facades\DB::table('team_trainers')->insert([
            'team_id'=>$team_id,
            'trainer_user_id'=>$trainer_i,
            'created_at'=>\Illuminate\Support\Carbon::now(),
            'updated_at'=>\Illuminate\Support\Carbon::now(),
        ]);


        \Illuminate\Support\Facades\DB::table('trainers')->insert([
            'user_id'=>$trainer_i,
            'country'=>3,
            'created_at'=>\Illuminate\Support\Carbon::now(),
            'updated_at'=>\Illuminate\Support\Carbon::now(),

        ]);
    }


    function addTrainer($limit){
        $faker = \Faker\Factory::create();
        for($i=0; $i<$limit; $i++){
            $user = new User();
            $user->first_name = 'Trainer ';
            $user->last_name =$faker->lastName;
            $user->email = $faker->email;
            $user->nationality_id =3;
            $user->password = bcrypt('password');
            $user->profile_picture = 'media/users/placeholder.png';
            $user->date_of_birth = $faker->dateTime();
            $user->verification_code = rand(2000, 10000);
            $user->verified_at = now();
            $user->save();
            $role = Role::where('name', 'trainer')->first();
            $user->assignRole($role);
        }
    }



    function addTeam(){
        $total = 1;
        for($i=0; $i<$total; $i++){
            $team = new \App\Team();
            $team->team_name = 'JOGO';
            $team->image = 'https://lh3.googleusercontent.com/KNyKMfQqqVcLYAROYJ6KPW7nqmyMMcuc7npdzuzYI9KXhnZDJ3Wkfqy_apcQTDgq2QlNp9LzqQly06N5qsNxUOLT';
            $team->description = 'The greatest club in the world';
            $team->save();
        }
    }
    private function levels()
    {
        \App\Level::insert([
            ['title' => 'level 1'],
            ['title' => 'level 2'],
            ['title' => 'level 3']
        ]);
    }

    private function skills()
    {
        \App\Skill::insert([
            ['name' => 'Agility'],
            ['name' => 'Ball control'],
            ['name' => 'skill 3']
        ]);
    }

    private function tools()
    {
        \App\Tool::insert([
            ['tool_name' => 'tool 1'],
            ['tool_name' => 'tool 2'],
            ['tool_name' => 'tool 3']
        ]);
    }

    private function categories()
    {
        \App\Category::insert([
            ['name' => 'category 1', 'description' => 'lorem ipsum'],
            ['name' => 'category 2', 'description' => 'lorem ipsum']
        ]);
    }

    private function createRoles()
    {
        \Spatie\Permission\Models\Role::insert([
            ['name' => 'player', 'guard_name' => 'api'],
            ['name' => 'trainer', 'guard_name' => 'api']
        ]);
    }

    private function createCountries()
    {


        $countries = array (
            0 =>
                array (
                    'name' => 'Afghanistan',
                    'dial_code' => '93',
                    'code' => 'AF',
                ),
            1 =>
                array (
                    'name' => 'Aland Islands',
                    'dial_code' => '358',
                    'code' => 'AX',
                ),
            2 =>
                array (
                    'name' => 'Albania',
                    'dial_code' => '355',
                    'code' => 'AL',
                ),
            3 =>
                array (
                    'name' => 'Algeria',
                    'dial_code' => '213',
                    'code' => 'DZ',
                ),
            4 =>
                array (
                    'name' => 'AmericanSamoa',
                    'dial_code' => '1684',
                    'code' => 'AS',
                ),
            5 =>
                array (
                    'name' => 'Andorra',
                    'dial_code' => '376',
                    'code' => 'AD',
                ),
            6 =>
                array (
                    'name' => 'Angola',
                    'dial_code' => '244',
                    'code' => 'AO',
                ),
            7 =>
                array (
                    'name' => 'Anguilla',
                    'dial_code' => '1264',
                    'code' => 'AI',
                ),
            8 =>
                array (
                    'name' => 'Antarctica',
                    'dial_code' => '672',
                    'code' => 'AQ',
                ),
            9 =>
                array (
                    'name' => 'Antigua and Barbuda',
                    'dial_code' => '1268',
                    'code' => 'AG',
                ),
            10 =>
                array (
                    'name' => 'Argentina',
                    'dial_code' => '54',
                    'code' => 'AR',
                ),
            11 =>
                array (
                    'name' => 'Armenia',
                    'dial_code' => '374',
                    'code' => 'AM',
                ),
            12 =>
                array (
                    'name' => 'Aruba',
                    'dial_code' => '297',
                    'code' => 'AW',
                ),
            13 =>
                array (
                    'name' => 'Australia',
                    'dial_code' => '61',
                    'code' => 'AU',
                ),
            14 =>
                array (
                    'name' => 'Austria',
                    'dial_code' => '43',
                    'code' => 'AT',
                ),
            15 =>
                array (
                    'name' => 'Azerbaijan',
                    'dial_code' => '994',
                    'code' => 'AZ',
                ),
            16 =>
                array (
                    'name' => 'Bahamas',
                    'dial_code' => '1242',
                    'code' => 'BS',
                ),
            17 =>
                array (
                    'name' => 'Bahrain',
                    'dial_code' => '973',
                    'code' => 'BH',
                ),
            18 =>
                array (
                    'name' => 'Bangladesh',
                    'dial_code' => '880',
                    'code' => 'BD',
                ),
            19 =>
                array (
                    'name' => 'Barbados',
                    'dial_code' => '1246',
                    'code' => 'BB',
                ),
            20 =>
                array (
                    'name' => 'Belarus',
                    'dial_code' => '375',
                    'code' => 'BY',
                ),
            21 =>
                array (
                    'name' => 'Belgium',
                    'dial_code' => '32',
                    'code' => 'BE',
                ),
            22 =>
                array (
                    'name' => 'Belize',
                    'dial_code' => '501',
                    'code' => 'BZ',
                ),
            23 =>
                array (
                    'name' => 'Benin',
                    'dial_code' => '229',
                    'code' => 'BJ',
                ),
            24 =>
                array (
                    'name' => 'Bermuda',
                    'dial_code' => '1441',
                    'code' => 'BM',
                ),
            25 =>
                array (
                    'name' => 'Bhutan',
                    'dial_code' => '975',
                    'code' => 'BT',
                ),
            26 =>
                array (
                    'name' => 'Bolivia, Plurinational State of',
                    'dial_code' => '591',
                    'code' => 'BO',
                ),
            27 =>
                array (
                    'name' => 'Bosnia and Herzegovina',
                    'dial_code' => '387',
                    'code' => 'BA',
                ),
            28 =>
                array (
                    'name' => 'Botswana',
                    'dial_code' => '267',
                    'code' => 'BW',
                ),
            29 =>
                array (
                    'name' => 'Brazil',
                    'dial_code' => '55',
                    'code' => 'BR',
                ),
            30 =>
                array (
                    'name' => 'British Indian Ocean Territory',
                    'dial_code' => '246',
                    'code' => 'IO',
                ),
            31 =>
                array (
                    'name' => 'Brunei Darussalam',
                    'dial_code' => '673',
                    'code' => 'BN',
                ),
            32 =>
                array (
                    'name' => 'Bulgaria',
                    'dial_code' => '359',
                    'code' => 'BG',
                ),
            33 =>
                array (
                    'name' => 'Burkina Faso',
                    'dial_code' => '226',
                    'code' => 'BF',
                ),
            34 =>
                array (
                    'name' => 'Burundi',
                    'dial_code' => '257',
                    'code' => 'BI',
                ),
            35 =>
                array (
                    'name' => 'Cambodia',
                    'dial_code' => '855',
                    'code' => 'KH',
                ),
            36 =>
                array (
                    'name' => 'Cameroon',
                    'dial_code' => '237',
                    'code' => 'CM',
                ),
            37 =>
                array (
                    'name' => 'Canada',
                    'dial_code' => '1',
                    'code' => 'CA',
                ),
            38 =>
                array (
                    'name' => 'Cape Verde',
                    'dial_code' => '238',
                    'code' => 'CV',
                ),
            39 =>
                array (
                    'name' => 'Cayman Islands',
                    'dial_code' => ' 345',
                    'code' => 'KY',
                ),
            40 =>
                array (
                    'name' => 'Central African Republic',
                    'dial_code' => '236',
                    'code' => 'CF',
                ),
            41 =>
                array (
                    'name' => 'Chad',
                    'dial_code' => '235',
                    'code' => 'TD',
                ),
            42 =>
                array (
                    'name' => 'Chile',
                    'dial_code' => '56',
                    'code' => 'CL',
                ),
            43 =>
                array (
                    'name' => 'China',
                    'dial_code' => '86',
                    'code' => 'CN',
                ),
            44 =>
                array (
                    'name' => 'Christmas Island',
                    'dial_code' => '61',
                    'code' => 'CX',
                ),
            45 =>
                array (
                    'name' => 'Cocos (Keeling) Islands',
                    'dial_code' => '61',
                    'code' => 'CC',
                ),
            46 =>
                array (
                    'name' => 'Colombia',
                    'dial_code' => '57',
                    'code' => 'CO',
                ),
            47 =>
                array (
                    'name' => 'Comoros',
                    'dial_code' => '269',
                    'code' => 'KM',
                ),
            48 =>
                array (
                    'name' => 'Congo',
                    'dial_code' => '242',
                    'code' => 'CG',
                ),
            49 =>
                array (
                    'name' => 'Congo, The Democratic Republic of the Congo',
                    'dial_code' => '243',
                    'code' => 'CD',
                ),
            50 =>
                array (
                    'name' => 'Cook Islands',
                    'dial_code' => '682',
                    'code' => 'CK',
                ),
            51 =>
                array (
                    'name' => 'Costa Rica',
                    'dial_code' => '506',
                    'code' => 'CR',
                ),
            52 =>
                array (
                    'name' => 'Cote d\'Ivoire',
                    'dial_code' => '225',
                    'code' => 'CI',
                ),
            53 =>
                array (
                    'name' => 'Croatia',
                    'dial_code' => '385',
                    'code' => 'HR',
                ),
            54 =>
                array (
                    'name' => 'Cuba',
                    'dial_code' => '53',
                    'code' => 'CU',
                ),
            55 =>
                array (
                    'name' => 'Cyprus',
                    'dial_code' => '357',
                    'code' => 'CY',
                ),
            56 =>
                array (
                    'name' => 'Czech Republic',
                    'dial_code' => '420',
                    'code' => 'CZ',
                ),
            57 =>
                array (
                    'name' => 'Denmark',
                    'dial_code' => '45',
                    'code' => 'DK',
                ),
            58 =>
                array (
                    'name' => 'Djibouti',
                    'dial_code' => '253',
                    'code' => 'DJ',
                ),
            59 =>
                array (
                    'name' => 'Dominica',
                    'dial_code' => '1767',
                    'code' => 'DM',
                ),
            60 =>
                array (
                    'name' => 'Dominican Republic',
                    'dial_code' => '1849',
                    'code' => 'DO',
                ),
            61 =>
                array (
                    'name' => 'Ecuador',
                    'dial_code' => '593',
                    'code' => 'EC',
                ),
            62 =>
                array (
                    'name' => 'Egypt',
                    'dial_code' => '20',
                    'code' => 'EG',
                ),
            63 =>
                array (
                    'name' => 'El Salvador',
                    'dial_code' => '503',
                    'code' => 'SV',
                ),
            64 =>
                array (
                    'name' => 'Equatorial Guinea',
                    'dial_code' => '240',
                    'code' => 'GQ',
                ),
            65 =>
                array (
                    'name' => 'Eritrea',
                    'dial_code' => '291',
                    'code' => 'ER',
                ),
            66 =>
                array (
                    'name' => 'Estonia',
                    'dial_code' => '372',
                    'code' => 'EE',
                ),
            67 =>
                array (
                    'name' => 'Ethiopia',
                    'dial_code' => '251',
                    'code' => 'ET',
                ),
            68 =>
                array (
                    'name' => 'Falkland Islands (Malvinas)',
                    'dial_code' => '500',
                    'code' => 'FK',
                ),
            69 =>
                array (
                    'name' => 'Faroe Islands',
                    'dial_code' => '298',
                    'code' => 'FO',
                ),
            70 =>
                array (
                    'name' => 'Fiji',
                    'dial_code' => '679',
                    'code' => 'FJ',
                ),
            71 =>
                array (
                    'name' => 'Finland',
                    'dial_code' => '358',
                    'code' => 'FI',
                ),
            72 =>
                array (
                    'name' => 'France',
                    'dial_code' => '33',
                    'code' => 'FR',
                ),
            73 =>
                array (
                    'name' => 'French Guiana',
                    'dial_code' => '594',
                    'code' => 'GF',
                ),
            74 =>
                array (
                    'name' => 'French Polynesia',
                    'dial_code' => '689',
                    'code' => 'PF',
                ),
            75 =>
                array (
                    'name' => 'Gabon',
                    'dial_code' => '241',
                    'code' => 'GA',
                ),
            76 =>
                array (
                    'name' => 'Gambia',
                    'dial_code' => '220',
                    'code' => 'GM',
                ),
            77 =>
                array (
                    'name' => 'Georgia',
                    'dial_code' => '995',
                    'code' => 'GE',
                ),
            78 =>
                array (
                    'name' => 'Germany',
                    'dial_code' => '49',
                    'code' => 'DE',
                ),
            79 =>
                array (
                    'name' => 'Ghana',
                    'dial_code' => '233',
                    'code' => 'GH',
                ),
            80 =>
                array (
                    'name' => 'Gibraltar',
                    'dial_code' => '350',
                    'code' => 'GI',
                ),
            81 =>
                array (
                    'name' => 'Greece',
                    'dial_code' => '30',
                    'code' => 'GR',
                ),
            82 =>
                array (
                    'name' => 'Greenland',
                    'dial_code' => '299',
                    'code' => 'GL',
                ),
            83 =>
                array (
                    'name' => 'Grenada',
                    'dial_code' => '1473',
                    'code' => 'GD',
                ),
            84 =>
                array (
                    'name' => 'Guadeloupe',
                    'dial_code' => '590',
                    'code' => 'GP',
                ),
            85 =>
                array (
                    'name' => 'Guam',
                    'dial_code' => '1671',
                    'code' => 'GU',
                ),
            86 =>
                array (
                    'name' => 'Guatemala',
                    'dial_code' => '502',
                    'code' => 'GT',
                ),
            87 =>
                array (
                    'name' => 'Guernsey',
                    'dial_code' => '44',
                    'code' => 'GG',
                ),
            88 =>
                array (
                    'name' => 'Guinea',
                    'dial_code' => '224',
                    'code' => 'GN',
                ),
            89 =>
                array (
                    'name' => 'Guinea-Bissau',
                    'dial_code' => '245',
                    'code' => 'GW',
                ),
            90 =>
                array (
                    'name' => 'Guyana',
                    'dial_code' => '595',
                    'code' => 'GY',
                ),
            91 =>
                array (
                    'name' => 'Haiti',
                    'dial_code' => '509',
                    'code' => 'HT',
                ),
            92 =>
                array (
                    'name' => 'Holy See (Vatican City State)',
                    'dial_code' => '379',
                    'code' => 'VA',
                ),
            93 =>
                array (
                    'name' => 'Honduras',
                    'dial_code' => '504',
                    'code' => 'HN',
                ),
            94 =>
                array (
                    'name' => 'Hong Kong',
                    'dial_code' => '852',
                    'code' => 'HK',
                ),
            95 =>
                array (
                    'name' => 'Hungary',
                    'dial_code' => '36',
                    'code' => 'HU',
                ),
            96 =>
                array (
                    'name' => 'Iceland',
                    'dial_code' => '354',
                    'code' => 'IS',
                ),
            97 =>
                array (
                    'name' => 'India',
                    'dial_code' => '91',
                    'code' => 'IN',
                ),
            98 =>
                array (
                    'name' => 'Indonesia',
                    'dial_code' => '62',
                    'code' => 'ID',
                ),
            99 =>
                array (
                    'name' => 'Iran, Islamic Republic of Persian Gulf',
                    'dial_code' => '98',
                    'code' => 'IR',
                ),
            100 =>
                array (
                    'name' => 'Iraq',
                    'dial_code' => '964',
                    'code' => 'IQ',
                ),
            101 =>
                array (
                    'name' => 'Ireland',
                    'dial_code' => '353',
                    'code' => 'IE',
                ),
            102 =>
                array (
                    'name' => 'Isle of Man',
                    'dial_code' => '44',
                    'code' => 'IM',
                ),
            103 =>
                array (
                    'name' => 'Israel',
                    'dial_code' => '972',
                    'code' => 'IL',
                ),
            104 =>
                array (
                    'name' => 'Italy',
                    'dial_code' => '39',
                    'code' => 'IT',
                ),
            105 =>
                array (
                    'name' => 'Jamaica',
                    'dial_code' => '1876',
                    'code' => 'JM',
                ),
            106 =>
                array (
                    'name' => 'Japan',
                    'dial_code' => '81',
                    'code' => 'JP',
                ),
            107 =>
                array (
                    'name' => 'Jersey',
                    'dial_code' => '44',
                    'code' => 'JE',
                ),
            108 =>
                array (
                    'name' => 'Jordan',
                    'dial_code' => '962',
                    'code' => 'JO',
                ),
            109 =>
                array (
                    'name' => 'Kazakhstan',
                    'dial_code' => '77',
                    'code' => 'KZ',
                ),
            110 =>
                array (
                    'name' => 'Kenya',
                    'dial_code' => '254',
                    'code' => 'KE',
                ),
            111 =>
                array (
                    'name' => 'Kiribati',
                    'dial_code' => '686',
                    'code' => 'KI',
                ),
            112 =>
                array (
                    'name' => 'Korea, Democratic People\'s Republic of Korea',
                    'dial_code' => '850',
                    'code' => 'KP',
                ),
            113 =>
                array (
                    'name' => 'Korea, Republic of South Korea',
                    'dial_code' => '82',
                    'code' => 'KR',
                ),
            114 =>
                array (
                    'name' => 'Kuwait',
                    'dial_code' => '965',
                    'code' => 'KW',
                ),
            115 =>
                array (
                    'name' => 'Kyrgyzstan',
                    'dial_code' => '996',
                    'code' => 'KG',
                ),
            116 =>
                array (
                    'name' => 'Laos',
                    'dial_code' => '856',
                    'code' => 'LA',
                ),
            117 =>
                array (
                    'name' => 'Latvia',
                    'dial_code' => '371',
                    'code' => 'LV',
                ),
            118 =>
                array (
                    'name' => 'Lebanon',
                    'dial_code' => '961',
                    'code' => 'LB',
                ),
            119 =>
                array (
                    'name' => 'Lesotho',
                    'dial_code' => '266',
                    'code' => 'LS',
                ),
            120 =>
                array (
                    'name' => 'Liberia',
                    'dial_code' => '231',
                    'code' => 'LR',
                ),
            121 =>
                array (
                    'name' => 'Libyan Arab Jamahiriya',
                    'dial_code' => '218',
                    'code' => 'LY',
                ),
            122 =>
                array (
                    'name' => 'Liechtenstein',
                    'dial_code' => '423',
                    'code' => 'LI',
                ),
            123 =>
                array (
                    'name' => 'Lithuania',
                    'dial_code' => '370',
                    'code' => 'LT',
                ),
            124 =>
                array (
                    'name' => 'Luxembourg',
                    'dial_code' => '352',
                    'code' => 'LU',
                ),
            125 =>
                array (
                    'name' => 'Macao',
                    'dial_code' => '853',
                    'code' => 'MO',
                ),
            126 =>
                array (
                    'name' => 'Macedonia',
                    'dial_code' => '389',
                    'code' => 'MK',
                ),
            127 =>
                array (
                    'name' => 'Madagascar',
                    'dial_code' => '261',
                    'code' => 'MG',
                ),
            128 =>
                array (
                    'name' => 'Malawi',
                    'dial_code' => '265',
                    'code' => 'MW',
                ),
            129 =>
                array (
                    'name' => 'Malaysia',
                    'dial_code' => '60',
                    'code' => 'MY',
                ),
            130 =>
                array (
                    'name' => 'Maldives',
                    'dial_code' => '960',
                    'code' => 'MV',
                ),
            131 =>
                array (
                    'name' => 'Mali',
                    'dial_code' => '223',
                    'code' => 'ML',
                ),
            132 =>
                array (
                    'name' => 'Malta',
                    'dial_code' => '356',
                    'code' => 'MT',
                ),
            133 =>
                array (
                    'name' => 'Marshall Islands',
                    'dial_code' => '692',
                    'code' => 'MH',
                ),
            134 =>
                array (
                    'name' => 'Martinique',
                    'dial_code' => '596',
                    'code' => 'MQ',
                ),
            135 =>
                array (
                    'name' => 'Mauritania',
                    'dial_code' => '222',
                    'code' => 'MR',
                ),
            136 =>
                array (
                    'name' => 'Mauritius',
                    'dial_code' => '230',
                    'code' => 'MU',
                ),
            137 =>
                array (
                    'name' => 'Mayotte',
                    'dial_code' => '262',
                    'code' => 'YT',
                ),
            138 =>
                array (
                    'name' => 'Mexico',
                    'dial_code' => '52',
                    'code' => 'MX',
                ),
            139 =>
                array (
                    'name' => 'Micronesia, Federated States of Micronesia',
                    'dial_code' => '691',
                    'code' => 'FM',
                ),
            140 =>
                array (
                    'name' => 'Moldova',
                    'dial_code' => '373',
                    'code' => 'MD',
                ),
            141 =>
                array (
                    'name' => 'Monaco',
                    'dial_code' => '377',
                    'code' => 'MC',
                ),
            142 =>
                array (
                    'name' => 'Mongolia',
                    'dial_code' => '976',
                    'code' => 'MN',
                ),
            143 =>
                array (
                    'name' => 'Montenegro',
                    'dial_code' => '382',
                    'code' => 'ME',
                ),
            144 =>
                array (
                    'name' => 'Montserrat',
                    'dial_code' => '1664',
                    'code' => 'MS',
                ),
            145 =>
                array (
                    'name' => 'Morocco',
                    'dial_code' => '212',
                    'code' => 'MA',
                ),
            146 =>
                array (
                    'name' => 'Mozambique',
                    'dial_code' => '258',
                    'code' => 'MZ',
                ),
            147 =>
                array (
                    'name' => 'Myanmar',
                    'dial_code' => '95',
                    'code' => 'MM',
                ),
            148 =>
                array (
                    'name' => 'Namibia',
                    'dial_code' => '264',
                    'code' => 'NA',
                ),
            149 =>
                array (
                    'name' => 'Nauru',
                    'dial_code' => '674',
                    'code' => 'NR',
                ),
            150 =>
                array (
                    'name' => 'Nepal',
                    'dial_code' => '977',
                    'code' => 'NP',
                ),
            151 =>
                array (
                    'name' => 'Netherlands',
                    'dial_code' => '31',
                    'code' => 'NL',
                ),
            152 =>
                array (
                    'name' => 'Netherlands Antilles',
                    'dial_code' => '599',
                    'code' => 'AN',
                ),
            153 =>
                array (
                    'name' => 'New Caledonia',
                    'dial_code' => '687',
                    'code' => 'NC',
                ),
            154 =>
                array (
                    'name' => 'New Zealand',
                    'dial_code' => '64',
                    'code' => 'NZ',
                ),
            155 =>
                array (
                    'name' => 'Nicaragua',
                    'dial_code' => '505',
                    'code' => 'NI',
                ),
            156 =>
                array (
                    'name' => 'Niger',
                    'dial_code' => '227',
                    'code' => 'NE',
                ),
            157 =>
                array (
                    'name' => 'Nigeria',
                    'dial_code' => '234',
                    'code' => 'NG',
                ),
            158 =>
                array (
                    'name' => 'Niue',
                    'dial_code' => '683',
                    'code' => 'NU',
                ),
            159 =>
                array (
                    'name' => 'Norfolk Island',
                    'dial_code' => '672',
                    'code' => 'NF',
                ),
            160 =>
                array (
                    'name' => 'Northern Mariana Islands',
                    'dial_code' => '1670',
                    'code' => 'MP',
                ),
            161 =>
                array (
                    'name' => 'Norway',
                    'dial_code' => '47',
                    'code' => 'NO',
                ),
            162 =>
                array (
                    'name' => 'Oman',
                    'dial_code' => '968',
                    'code' => 'OM',
                ),
            163 =>
                array (
                    'name' => 'Pakistan',
                    'dial_code' => '92',
                    'code' => 'PK',
                ),
            164 =>
                array (
                    'name' => 'Palau',
                    'dial_code' => '680',
                    'code' => 'PW',
                ),
            165 =>
                array (
                    'name' => 'Palestinian Territory, Occupied',
                    'dial_code' => '970',
                    'code' => 'PS',
                ),
            166 =>
                array (
                    'name' => 'Panama',
                    'dial_code' => '507',
                    'code' => 'PA',
                ),
            167 =>
                array (
                    'name' => 'Papua New Guinea',
                    'dial_code' => '675',
                    'code' => 'PG',
                ),
            168 =>
                array (
                    'name' => 'Paraguay',
                    'dial_code' => '595',
                    'code' => 'PY',
                ),
            169 =>
                array (
                    'name' => 'Peru',
                    'dial_code' => '51',
                    'code' => 'PE',
                ),
            170 =>
                array (
                    'name' => 'Philippines',
                    'dial_code' => '63',
                    'code' => 'PH',
                ),
            171 =>
                array (
                    'name' => 'Pitcairn',
                    'dial_code' => '872',
                    'code' => 'PN',
                ),
            172 =>
                array (
                    'name' => 'Poland',
                    'dial_code' => '48',
                    'code' => 'PL',
                ),
            173 =>
                array (
                    'name' => 'Portugal',
                    'dial_code' => '351',
                    'code' => 'PT',
                ),
            174 =>
                array (
                    'name' => 'Puerto Rico',
                    'dial_code' => '1939',
                    'code' => 'PR',
                ),
            175 =>
                array (
                    'name' => 'Qatar',
                    'dial_code' => '974',
                    'code' => 'QA',
                ),
            176 =>
                array (
                    'name' => 'Romania',
                    'dial_code' => '40',
                    'code' => 'RO',
                ),
            177 =>
                array (
                    'name' => 'Russia',
                    'dial_code' => '7',
                    'code' => 'RU',
                ),
            178 =>
                array (
                    'name' => 'Rwanda',
                    'dial_code' => '250',
                    'code' => 'RW',
                ),
            179 =>
                array (
                    'name' => 'Reunion',
                    'dial_code' => '262',
                    'code' => 'RE',
                ),
            180 =>
                array (
                    'name' => 'Saint Barthelemy',
                    'dial_code' => '590',
                    'code' => 'BL',
                ),
            181 =>
                array (
                    'name' => 'Saint Helena, Ascension and Tristan Da Cunha',
                    'dial_code' => '290',
                    'code' => 'SH',
                ),
            182 =>
                array (
                    'name' => 'Saint Kitts and Nevis',
                    'dial_code' => '1869',
                    'code' => 'KN',
                ),
            183 =>
                array (
                    'name' => 'Saint Lucia',
                    'dial_code' => '1758',
                    'code' => 'LC',
                ),
            184 =>
                array (
                    'name' => 'Saint Martin',
                    'dial_code' => '590',
                    'code' => 'MF',
                ),
            185 =>
                array (
                    'name' => 'Saint Pierre and Miquelon',
                    'dial_code' => '508',
                    'code' => 'PM',
                ),
            186 =>
                array (
                    'name' => 'Saint Vincent and the Grenadines',
                    'dial_code' => '1784',
                    'code' => 'VC',
                ),
            187 =>
                array (
                    'name' => 'Samoa',
                    'dial_code' => '685',
                    'code' => 'WS',
                ),
            188 =>
                array (
                    'name' => 'San Marino',
                    'dial_code' => '378',
                    'code' => 'SM',
                ),
            189 =>
                array (
                    'name' => 'Sao Tome and Principe',
                    'dial_code' => '239',
                    'code' => 'ST',
                ),
            190 =>
                array (
                    'name' => 'Saudi Arabia',
                    'dial_code' => '966',
                    'code' => 'SA',
                ),
            191 =>
                array (
                    'name' => 'Senegal',
                    'dial_code' => '221',
                    'code' => 'SN',
                ),
            192 =>
                array (
                    'name' => 'Serbia',
                    'dial_code' => '381',
                    'code' => 'RS',
                ),
            193 =>
                array (
                    'name' => 'Seychelles',
                    'dial_code' => '248',
                    'code' => 'SC',
                ),
            194 =>
                array (
                    'name' => 'Sierra Leone',
                    'dial_code' => '232',
                    'code' => 'SL',
                ),
            195 =>
                array (
                    'name' => 'Singapore',
                    'dial_code' => '65',
                    'code' => 'SG',
                ),
            196 =>
                array (
                    'name' => 'Slovakia',
                    'dial_code' => '421',
                    'code' => 'SK',
                ),
            197 =>
                array (
                    'name' => 'Slovenia',
                    'dial_code' => '386',
                    'code' => 'SI',
                ),
            198 =>
                array (
                    'name' => 'Solomon Islands',
                    'dial_code' => '677',
                    'code' => 'SB',
                ),
            199 =>
                array (
                    'name' => 'Somalia',
                    'dial_code' => '252',
                    'code' => 'SO',
                ),
            200 =>
                array (
                    'name' => 'South Africa',
                    'dial_code' => '27',
                    'code' => 'ZA',
                ),
            201 =>
                array (
                    'name' => 'South Sudan',
                    'dial_code' => '211',
                    'code' => 'SS',
                ),
            202 =>
                array (
                    'name' => 'South Georgia and the South Sandwich Islands',
                    'dial_code' => '500',
                    'code' => 'GS',
                ),
            203 =>
                array (
                    'name' => 'Spain',
                    'dial_code' => '34',
                    'code' => 'ES',
                ),
            204 =>
                array (
                    'name' => 'Sri Lanka',
                    'dial_code' => '94',
                    'code' => 'LK',
                ),
            205 =>
                array (
                    'name' => 'Sudan',
                    'dial_code' => '249',
                    'code' => 'SD',
                ),
            206 =>
                array (
                    'name' => 'Suriname',
                    'dial_code' => '597',
                    'code' => 'SR',
                ),
            207 =>
                array (
                    'name' => 'Svalbard and Jan Mayen',
                    'dial_code' => '47',
                    'code' => 'SJ',
                ),
            208 =>
                array (
                    'name' => 'Swaziland',
                    'dial_code' => '268',
                    'code' => 'SZ',
                ),
            209 =>
                array (
                    'name' => 'Sweden',
                    'dial_code' => '46',
                    'code' => 'SE',
                ),
            210 =>
                array (
                    'name' => 'Switzerland',
                    'dial_code' => '41',
                    'code' => 'CH',
                ),
            211 =>
                array (
                    'name' => 'Syrian Arab Republic',
                    'dial_code' => '963',
                    'code' => 'SY',
                ),
            212 =>
                array (
                    'name' => 'Taiwan',
                    'dial_code' => '886',
                    'code' => 'TW',
                ),
            213 =>
                array (
                    'name' => 'Tajikistan',
                    'dial_code' => '992',
                    'code' => 'TJ',
                ),
            214 =>
                array (
                    'name' => 'Tanzania, United Republic of Tanzania',
                    'dial_code' => '255',
                    'code' => 'TZ',
                ),
            215 =>
                array (
                    'name' => 'Thailand',
                    'dial_code' => '66',
                    'code' => 'TH',
                ),
            216 =>
                array (
                    'name' => 'Timor-Leste',
                    'dial_code' => '670',
                    'code' => 'TL',
                ),
            217 =>
                array (
                    'name' => 'Togo',
                    'dial_code' => '228',
                    'code' => 'TG',
                ),
            218 =>
                array (
                    'name' => 'Tokelau',
                    'dial_code' => '690',
                    'code' => 'TK',
                ),
            219 =>
                array (
                    'name' => 'Tonga',
                    'dial_code' => '676',
                    'code' => 'TO',
                ),
            220 =>
                array (
                    'name' => 'Trinidad and Tobago',
                    'dial_code' => '1868',
                    'code' => 'TT',
                ),
            221 =>
                array (
                    'name' => 'Tunisia',
                    'dial_code' => '216',
                    'code' => 'TN',
                ),
            222 =>
                array (
                    'name' => 'Turkey',
                    'dial_code' => '90',
                    'code' => 'TR',
                ),
            223 =>
                array (
                    'name' => 'Turkmenistan',
                    'dial_code' => '993',
                    'code' => 'TM',
                ),
            224 =>
                array (
                    'name' => 'Turks and Caicos Islands',
                    'dial_code' => '1649',
                    'code' => 'TC',
                ),
            225 =>
                array (
                    'name' => 'Tuvalu',
                    'dial_code' => '688',
                    'code' => 'TV',
                ),
            226 =>
                array (
                    'name' => 'Uganda',
                    'dial_code' => '256',
                    'code' => 'UG',
                ),
            227 =>
                array (
                    'name' => 'Ukraine',
                    'dial_code' => '380',
                    'code' => 'UA',
                ),
            228 =>
                array (
                    'name' => 'United Arab Emirates',
                    'dial_code' => '971',
                    'code' => 'AE',
                ),
            229 =>
                array (
                    'name' => 'United Kingdom',
                    'dial_code' => '44',
                    'code' => 'GB',
                ),
            230 =>
                array (
                    'name' => 'United States',
                    'dial_code' => '1',
                    'code' => 'US',
                ),
            231 =>
                array (
                    'name' => 'Uruguay',
                    'dial_code' => '598',
                    'code' => 'UY',
                ),
            232 =>
                array (
                    'name' => 'Uzbekistan',
                    'dial_code' => '998',
                    'code' => 'UZ',
                ),
            233 =>
                array (
                    'name' => 'Vanuatu',
                    'dial_code' => '678',
                    'code' => 'VU',
                ),
            234 =>
                array (
                    'name' => 'Venezuela, Bolivarian Republic of Venezuela',
                    'dial_code' => '58',
                    'code' => 'VE',
                ),
            235 =>
                array (
                    'name' => 'Vietnam',
                    'dial_code' => '84',
                    'code' => 'VN',
                ),
            236 =>
                array (
                    'name' => 'Virgin Islands, British',
                    'dial_code' => '1284',
                    'code' => 'VG',
                ),
            237 =>
                array (
                    'name' => 'Virgin Islands, U.S.',
                    'dial_code' => '1340',
                    'code' => 'VI',
                ),
            238 =>
                array (
                    'name' => 'Wallis and Futuna',
                    'dial_code' => '681',
                    'code' => 'WF',
                ),
            239 =>
                array (
                    'name' => 'Yemen',
                    'dial_code' => '967',
                    'code' => 'YE',
                ),
            240 =>
                array (
                    'name' => 'Zambia',
                    'dial_code' => '260',
                    'code' => 'ZM',
                ),
            241 =>
                array (
                    'name' => 'Zimbabwe',
                    'dial_code' => '263',
                    'code' => 'ZW',
                ),
        );

        foreach ($countries as $country){

            $name = ['en'=> $country['name'], 'nl' => $country['name']];
            \App\Country::insert([
                ['name' =>json_encode($name), 'iso' => $country['code'], 'phone_code' => (int)$country['dial_code'] ],
            ]);
        }

    }

    private function createCustomaryFoot()
    {
        \App\CustomaryFoot::insert([
            ['name' => 'Left'],
            ['name' => 'Right']
        ]);
    }

    private function createPositions()
    {
        \App\Position::insert([
            ['name' => 'Left Back'],
            ['name' => 'Right Back']
        ]);
    }

    private function createStatus()
    {
        Status::insert([
            ['name' => 'active'],
            ['name' => 'inactive'],
            ['name' => 'completed'],
            ['name' => 'pending'],
            ['name' => 'blocked'],
            ['name' => 'read'],
            ['name' => 'unread']
        ]);
    }
    private function access_modifiers()
    {
        \App\AccessModifier::insert([
            ['name' => 'public','display_name'=>'Public','description'=>'Anyone on Jogo can view your profile. Your exercises are visible to anyone on Jogo by default. Follow requests are accepted automatically'],
            ['name' => 'private','display_name'=>'Private','description'=>'Only you can view your profile. Your exercises are visible only to you. You must approve requests to be followedType a message'],
            ['name' => 'follower','display_name'=>'Followers','description'=>'Followers can view your profile. Your exercises are visible to followers. You must approve requests to be followed']
        ]);

    }


}
