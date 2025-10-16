<?php
namespace App\Models\User;

trait UserAccessor {

    public function getFullPhoneAttribute() {
        return (optional($this->phone_country)->phone_code != NULL) ? optional($this->phone_country)->phone_code.'-'.$this->phone : $this->phone;
    }
}
