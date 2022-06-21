<?php

namespace Database\Seeders;

use App\Models\EquipmentCategory;
use App\Models\Language;
use App\Models\LanguageKey;
use App\Models\LanguageMlv;
use App\Models\RequestReason;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();


        $u = new User();
        $u->name = "Salim S";
        $u->email = "salim.zepcom@gmail.com";
        $u->designation = "Senior Software Engineer (WordPress/PHP)";
        $u->password = Hash::make( "Admin@123");
        $u->parent_id = 0;
        $u->created_by = '0';
        $u->save();

        $l = new Language();
        $l->name = "English";
        $l->code = "en";
        $l->status = "active";
        $l->save();

        $lk = new LanguageKey();
        $lk->key = "success";
        $lk->status = "active";
        $lk->save();

        $lmlv = new LanguageMlv();
        $lmlv->language_id = $l->id;
        $lmlv->language_key_id = $lk->id;
        $lmlv->status = "active";
        $lmlv->value = "Success!";
        $lmlv->save();

        $lk = new LanguageKey();
        $lk->key = "invalid_parameters";
        $lk->status = "active";
        $lk->save();

        $lmlv = new LanguageMlv();
        $lmlv->language_id = $l->id;
        $lmlv->language_key_id = $lk->id;
        $lmlv->status = "active";
        $lmlv->value = "Invalid Parameters!";
        $lmlv->save();


        //Equipment Category

        $ec = new EquipmentCategory();
        $ec->name = "Laptop Malfunction";
        $ec->display_name = "Laptop Malfunction";
        $ec->status = "active";
        $ec->is_public = "1";
        $ec->save();

        $ec = new EquipmentCategory();
        $ec->name = "Mouse";
        $ec->display_name = "Mouse";
        $ec->status = "active";
        $ec->is_public = "1";
        $ec->save();

        $ec = new EquipmentCategory();
        $ec->name = "Keyboard";
        $ec->display_name = "Keyboard";
        $ec->status = "active";
        $ec->is_public = "1";
        $ec->save();

        $ec = new EquipmentCategory();
        $ec->name = "Additional LCD";
        $ec->display_name = "Additional LCD";
        $ec->status = "active";
        $ec->is_public = "1";
        $ec->save();

        $ec = new EquipmentCategory();
        $ec->name = "Laptop Upgradation";
        $ec->display_name = "Laptop Upgradation";
        $ec->status = "active";
        $ec->is_public = "1";
        $ec->save();

        //RequestReason

        $rr = new RequestReason();
        $rr->name = "Hardware issue";
        $rr->display_name = "Hardware issue";
        $rr->status = "active";
        $rr->is_public = "1";
        $rr->save();

        $rr = new RequestReason();
        $rr->name = "HDD to SSD / SSD Issue";
        $rr->display_name = "HDD to SSD / SSD Issue";
        $rr->status = "active";
        $rr->is_public = "1";
        $rr->save();

        $rr = new RequestReason();
        $rr->name = "LED / Screen";
        $rr->display_name = "LED / Screen";
        $rr->status = "active";
        $rr->is_public = "1";
        $rr->save();

        $rr = new RequestReason();
        $rr->name = "Keyboard";
        $rr->display_name = "Keyboard";
        $rr->status = "active";
        $rr->is_public = "1";
        $rr->save();

        $rr = new RequestReason();
        $rr->name = "Battery";
        $rr->display_name = "Battery";
        $rr->status = "active";
        $rr->is_public = "1";
        $rr->save();

    }
}
