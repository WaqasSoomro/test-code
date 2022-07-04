<?php

namespace App\Http\Controllers\Api;

use App\Country;
use App\CustomaryFoot;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Position;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Get Data
 *
 * get countries, positions, customary_foots data by giving value 'get'
 *
 */
class WantController extends Controller
{
    /**
     * Get Data
     *
     * @response {
        "Response": true,
        "StatusCode": 200,
        "Message": "Records found",
        "Result": {
            "countries": [
                {
                    "id": 1,
                    "name": "Afghanistan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 2,
                    "name": "Aland Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 3,
                    "name": "Albania",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 4,
                    "name": "Algeria",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 5,
                    "name": "AmericanSamoa",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 6,
                    "name": "Andorra",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 7,
                    "name": "Angola",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 8,
                    "name": "Anguilla",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 9,
                    "name": "Antarctica",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 10,
                    "name": "Antigua and Barbuda",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 11,
                    "name": "Argentina",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 12,
                    "name": "Armenia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 13,
                    "name": "Aruba",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 14,
                    "name": "Australia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 15,
                    "name": "Austria",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 16,
                    "name": "Azerbaijan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 17,
                    "name": "Bahamas",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 18,
                    "name": "Bahrain",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 19,
                    "name": "Bangladesh",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 20,
                    "name": "Barbados",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 21,
                    "name": "Belarus",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 22,
                    "name": "Belgium",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 23,
                    "name": "Belize",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 24,
                    "name": "Benin",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 25,
                    "name": "Bermuda",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 26,
                    "name": "Bhutan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 27,
                    "name": "Bolivia, Plurinational State of",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 28,
                    "name": "Bosnia and Herzegovina",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 29,
                    "name": "Botswana",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 30,
                    "name": "Brazil",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 31,
                    "name": "British Indian Ocean Territory",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 32,
                    "name": "Brunei Darussalam",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 33,
                    "name": "Bulgaria",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 34,
                    "name": "Burkina Faso",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 35,
                    "name": "Burundi",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 36,
                    "name": "Cambodia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 37,
                    "name": "Cameroon",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 38,
                    "name": "Canada",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 39,
                    "name": "Cape Verde",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 40,
                    "name": "Cayman Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 41,
                    "name": "Central African Republic",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 42,
                    "name": "Chad",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 43,
                    "name": "Chile",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 44,
                    "name": "China",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 45,
                    "name": "Christmas Island",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 46,
                    "name": "Cocos (Keeling) Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 47,
                    "name": "Colombia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 48,
                    "name": "Comoros",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 50,
                    "name": "Congo, The Democratic Republic of the Congo",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 49,
                    "name": "Congo",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 51,
                    "name": "Cook Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 52,
                    "name": "Costa Rica",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 53,
                    "name": "Cote d'Ivoire",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 54,
                    "name": "Croatia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 55,
                    "name": "Cuba",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 56,
                    "name": "Cyprus",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 57,
                    "name": "Czech Republic",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 58,
                    "name": "Denmark",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 59,
                    "name": "Djibouti",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 60,
                    "name": "Dominica",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 61,
                    "name": "Dominican Republic",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 62,
                    "name": "Ecuador",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 63,
                    "name": "Egypt",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 64,
                    "name": "El Salvador",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 65,
                    "name": "Equatorial Guinea",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 66,
                    "name": "Eritrea",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 67,
                    "name": "Estonia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 68,
                    "name": "Ethiopia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 69,
                    "name": "Falkland Islands (Malvinas)",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 70,
                    "name": "Faroe Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 71,
                    "name": "Fiji",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 72,
                    "name": "Finland",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 73,
                    "name": "France",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 74,
                    "name": "French Guiana",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 75,
                    "name": "French Polynesia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 76,
                    "name": "Gabon",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 77,
                    "name": "Gambia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 78,
                    "name": "Georgia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 79,
                    "name": "Germany",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 80,
                    "name": "Ghana",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 81,
                    "name": "Gibraltar",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 82,
                    "name": "Greece",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 83,
                    "name": "Greenland",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 84,
                    "name": "Grenada",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 85,
                    "name": "Guadeloupe",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 86,
                    "name": "Guam",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 87,
                    "name": "Guatemala",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 88,
                    "name": "Guernsey",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 90,
                    "name": "Guinea-Bissau",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 89,
                    "name": "Guinea",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 91,
                    "name": "Guyana",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 92,
                    "name": "Haiti",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 93,
                    "name": "Holy See (Vatican City State)",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 94,
                    "name": "Honduras",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 95,
                    "name": "Hong Kong",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 96,
                    "name": "Hungary",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 97,
                    "name": "Iceland",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 98,
                    "name": "India",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 99,
                    "name": "Indonesia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 100,
                    "name": "Iran, Islamic Republic of Persian Gulf",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 101,
                    "name": "Iraq",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 102,
                    "name": "Ireland",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 103,
                    "name": "Isle of Man",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 104,
                    "name": "Israel",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 105,
                    "name": "Italy",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 106,
                    "name": "Jamaica",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 107,
                    "name": "Japan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 108,
                    "name": "Jersey",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 109,
                    "name": "Jordan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 110,
                    "name": "Kazakhstan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 111,
                    "name": "Kenya",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 112,
                    "name": "Kiribati",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 113,
                    "name": "Korea, Democratic People's Republic of Korea",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 114,
                    "name": "Korea, Republic of South Korea",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 115,
                    "name": "Kuwait",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 116,
                    "name": "Kyrgyzstan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 117,
                    "name": "Laos",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 118,
                    "name": "Latvia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 119,
                    "name": "Lebanon",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 120,
                    "name": "Lesotho",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 121,
                    "name": "Liberia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 122,
                    "name": "Libyan Arab Jamahiriya",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 123,
                    "name": "Liechtenstein",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 124,
                    "name": "Lithuania",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 125,
                    "name": "Luxembourg",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 126,
                    "name": "Macao",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 127,
                    "name": "Macedonia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 128,
                    "name": "Madagascar",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 129,
                    "name": "Malawi",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 130,
                    "name": "Malaysia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 131,
                    "name": "Maldives",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 132,
                    "name": "Mali",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 133,
                    "name": "Malta",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 134,
                    "name": "Marshall Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 135,
                    "name": "Martinique",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 136,
                    "name": "Mauritania",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 137,
                    "name": "Mauritius",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 138,
                    "name": "Mayotte",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 139,
                    "name": "Mexico",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 140,
                    "name": "Micronesia, Federated States of Micronesia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 141,
                    "name": "Moldova",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 142,
                    "name": "Monaco",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 143,
                    "name": "Mongolia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 144,
                    "name": "Montenegro",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 145,
                    "name": "Montserrat",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 146,
                    "name": "Morocco",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 147,
                    "name": "Mozambique",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 148,
                    "name": "Myanmar",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 149,
                    "name": "Namibia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 150,
                    "name": "Nauru",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 151,
                    "name": "Nepal",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 153,
                    "name": "Netherlands Antilles",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 152,
                    "name": "Netherlands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 154,
                    "name": "New Caledonia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 155,
                    "name": "New Zealand",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 156,
                    "name": "Nicaragua",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 157,
                    "name": "Niger",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 158,
                    "name": "Nigeria",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 159,
                    "name": "Niue",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 160,
                    "name": "Norfolk Island",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 161,
                    "name": "Northern Mariana Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 162,
                    "name": "Norway",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 163,
                    "name": "Oman",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 164,
                    "name": "Pakistan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 165,
                    "name": "Palau",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 166,
                    "name": "Palestinian Territory, Occupied",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 167,
                    "name": "Panama",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 168,
                    "name": "Papua New Guinea",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 169,
                    "name": "Paraguay",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 170,
                    "name": "Peru",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 171,
                    "name": "Philippines",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 172,
                    "name": "Pitcairn",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 173,
                    "name": "Poland",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 174,
                    "name": "Portugal",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 175,
                    "name": "Puerto Rico",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 176,
                    "name": "Qatar",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 180,
                    "name": "Reunion",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 177,
                    "name": "Romania",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 178,
                    "name": "Russia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 179,
                    "name": "Rwanda",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 181,
                    "name": "Saint Barthelemy",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 182,
                    "name": "Saint Helena, Ascension and Tristan Da Cunha",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 183,
                    "name": "Saint Kitts and Nevis",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 184,
                    "name": "Saint Lucia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 185,
                    "name": "Saint Martin",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 186,
                    "name": "Saint Pierre and Miquelon",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 187,
                    "name": "Saint Vincent and the Grenadines",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 188,
                    "name": "Samoa",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 189,
                    "name": "San Marino",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 190,
                    "name": "Sao Tome and Principe",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 191,
                    "name": "Saudi Arabia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 192,
                    "name": "Senegal",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 193,
                    "name": "Serbia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 194,
                    "name": "Seychelles",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 195,
                    "name": "Sierra Leone",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 196,
                    "name": "Singapore",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 197,
                    "name": "Slovakia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 198,
                    "name": "Slovenia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 199,
                    "name": "Solomon Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 200,
                    "name": "Somalia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 201,
                    "name": "South Africa",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 203,
                    "name": "South Georgia and the South Sandwich Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 202,
                    "name": "South Sudan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 204,
                    "name": "Spain",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 205,
                    "name": "Sri Lanka",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 206,
                    "name": "Sudan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 207,
                    "name": "Suriname",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 208,
                    "name": "Svalbard and Jan Mayen",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 209,
                    "name": "Swaziland",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 210,
                    "name": "Sweden",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 211,
                    "name": "Switzerland",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 212,
                    "name": "Syrian Arab Republic",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 213,
                    "name": "Taiwan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 214,
                    "name": "Tajikistan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 215,
                    "name": "Tanzania, United Republic of Tanzania",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 216,
                    "name": "Thailand",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 217,
                    "name": "Timor-Leste",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 218,
                    "name": "Togo",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 219,
                    "name": "Tokelau",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 220,
                    "name": "Tonga",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 221,
                    "name": "Trinidad and Tobago",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 222,
                    "name": "Tunisia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 223,
                    "name": "Turkey",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 224,
                    "name": "Turkmenistan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 225,
                    "name": "Turks and Caicos Islands",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 226,
                    "name": "Tuvalu",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 227,
                    "name": "Uganda",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 228,
                    "name": "Ukraine",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 229,
                    "name": "United Arab Emirates",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 230,
                    "name": "United Kingdom",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 231,
                    "name": "United States",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 232,
                    "name": "Uruguay",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 233,
                    "name": "Uzbekistan",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 234,
                    "name": "Vanuatu",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 235,
                    "name": "Venezuela, Bolivarian Republic of Venezuela",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 236,
                    "name": "Vietnam",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 237,
                    "name": "Virgin Islands, British",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 238,
                    "name": "Virgin Islands, U.S.",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 239,
                    "name": "Wallis and Futuna",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 240,
                    "name": "Yemen",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 241,
                    "name": "Zambia",
                    "flag": "https://flagcdn.com/w160/.png"
                },
                {
                    "id": 242,
                    "name": "Zimbabwe",
                    "flag": "https://flagcdn.com/w160/.png"
                }
            ],
            "customary_foots": [
                {
                    "id": 1,
                    "name": "Left"
                },
                {
                    "id": 2,
                    "name": "Right"
                }
            ],
            "positions": []
        }
    }
     *
     * @urlParam countries optional value To get the countries, value should be 'get'
     * @urlParam customary_foots optional value To get the customary_foots, value should be 'get'
     * @urlParam positions optional value To get the positions, value should be 'get'
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $data['countries'] = [];
        $data['customary_foots'] = [];
        $data['positions'] = [];

        if ($request->countries == 'get') {
            $data['countries'] = Country::select('id', 'name','iso as flag')->orderBy('name', 'asc')->get();
        }
        if ($request->customary_foots == 'get') {
            $data['customary_foots'] = CustomaryFoot::select('id', 'name')->get();
        }
        if ($request->positions == 'get') {
            $data['positions'] = Position::select('id', 'name')->get();
        }

        return Helper::apiSuccessResponse(true, 'Records found', $data);
    }
}
