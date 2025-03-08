<?php
// lib/url.php

// URL de base (pour les liens HTML uniquement)
define('BASE_URL', '/eemi/secondeannée/php/RTY/RTY/eemi-2A-PHP/');

// URLs publiques (pour HTML)
function url($path = '') {
    return BASE_URL . 'public/' . ltrim($path, '/');
}

function index() {
    return url('index.php');
}

function cart() {
    return url('cart.php');
}

function add(){
    return url('add_to_cart.php');
}

function checkout() {
    return url('checkout.php');
}

function produit($id = null) {
    return url('produit.php' . ($id ? '?id=' . intval($id) : ''));
}

function login() {
    return url('login.php');
}

function logout() {
    return url('logout.php');
}

function register() {
    return url('register.php');
}

function url_order_success() {
    return url('order_success.php');
}
function profil(){
    return url('profil.php');
}

// URLs Admin (pour HTML)
function admin_url($path = '') {
    return BASE_URL . 'admin/' . ltrim($path, '/');
}

function url_admin_dashboard() {
    return admin_url('admin.php');
}

function url_admin_stock() {
    return admin_url('stock.php');
}

function url_admin_modif() {
    return admin_url('modif.php');
}

// Assets (images, CSS, JS...) pour HTML
function asset($assetPath = '') {
    return BASE_URL . ltrim($assetPath, '/');
}

// -------------------------- IMPORTANT ----------------------------

// Chemins du système de fichiers (pour require/include PHP uniquement)
function lib_path($path = '') {
    return __DIR__ . '/' . ltrim($path, '/');
}

function path_lib_db() {
    return lib_path('db.php');
}

function path_lib_register_login() {
    return lib_path('register_login.php');
}

function path_lib_graphic_logout() {
    return lib_path('graphic_logout.php');
}

// Pour les chemins vers les scripts PHP spécifiques (ajoutés explicitement)
function path_add_to_cart() {
    return lib_path('../public/add_to_cart.php');
}

function path_checkout() {
    return lib_path('checkout.php');
}

function path_order_success() {
    return lib_path('order_success.php');
}
