<?php
// filepath: c:\xampp\htdocs\tamayo\FinalNagidNi\classes\DeliveryPerson.php

require_once 'User.php';

class DeliveryPerson extends User {
    private $orders;
    private $vehicleType;

    public function __construct($id, $name, $vehicleType) {
        parent::__construct($id, $name, 'delivery_person');
        $this->vehicleType = $vehicleType;
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

    public function getVehicleType() {
        return $this->vehicleType;
    }

    public function setOrderStatusToOutForDelivery($index) {
        if (isset($this->orders[$index])) {
            $this->orders[$index]['deliveryStatus'] = 'Out for Delivery';
            $this->saveOrders();
        }
    }
    
    public function setOrderStatusToDelivered($index) {
        if (isset($this->orders[$index])) {
            $this->orders[$index]['deliveryStatus'] = 'Delivered';
            $this->saveOrders();
        }
    }
    

    public function updateOrderStatus($index, $status) {
        if (isset($this->orders[$index])) {
            $this->orders[$index]['status'] = $status;
            $this->saveOrders();
        }
    }

    public function markOrderAsPaid(&$orders, $index) {
        if (isset($orders[$index]) && $orders[$index]['status'] === 'Not Paid') {
            $orders[$index]['status'] = 'Paid';
            $this->saveOrders();
        }
    }
}