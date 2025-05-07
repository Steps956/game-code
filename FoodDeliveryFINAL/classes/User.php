<?php
class User {
    public $id;
    public $name;
    public $role;

    public function __construct($id, $name, $role) {
        $this->id = $id;
        $this->name = $name;
        $this->role = $role;
    }

    public function isAdmin() {
        return $this->role === 'admin';
    }

    public function isCustomer() {
        return $this->role === 'customer';
    }

    public function isEmployee() {
        return $this->role === 'employee';
    }

    public function isDeliveryPerson() {
        return $this->role === 'delivery_person';
    }
}
?>