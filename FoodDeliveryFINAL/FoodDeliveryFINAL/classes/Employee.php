<?php
require_once 'User.php';

class Employee extends User {
    private $orders;

    public function __construct($id, $name) {
        parent::__construct($id, $name, 'employee');
        $this->orders = $this->loadOrders();
    }

    private function loadOrders() {
        return file_exists('data/orders.json') 
            ? json_decode(file_get_contents('data/orders.json'), true) 
            : [];
    }

    private function saveOrders() {
        file_put_contents('data/orders.json', json_encode($this->orders, JSON_PRETTY_PRINT));
    }

    public function getOrders() {
        return $this->orders;
    }

    public function deleteOrder($index) {
        if (isset($this->orders[$index])) {
            unset($this->orders[$index]);
            $this->orders = array_values($this->orders); // Reindex the array
            $this->saveOrders();
        }
    }

    public function assignDeliveryDriver($index, $driverName) {
        if (isset($this->orders[$index])) {
            $this->orders[$index]['deliveryDriver'] = $driverName;
            $this->orders[$index]['deliveryStatus'] = 'Assigned to Driver';
            $this->saveOrders();
        }
    }
}
?>