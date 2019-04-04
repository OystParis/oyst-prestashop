<?php

namespace Oyst\Services;

use Configuration;
use Exception;
use Tools;
use Validate;

class ObjectService {

    private static $instance;
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new ObjectService();
        }
        return self::$instance;
    }

    private function __construct() {}

    private function __clone() {}

    public function getRequiredFields($object_name)
    {
        $required_fields = array();
        foreach ($object_name::$definition['fields'] as $field_name => $field) {
            //Exception
            if ($object_name == 'Customer' && $field_name == 'passwd') {
                continue;
            }
            if (isset($field['required']) && $field['required']) {
                $required_fields[] = $field_name;
            }
        }
        return $required_fields;
    }

    public function createObject($object_name, $fields)
    {
        $errors = array();
        $object = null;
        $id = 0;
        $object_required_fields = $this->getRequiredFields($object_name);
        foreach ($object_required_fields as $object_required_field) {
            if (!isset($fields[$object_required_field])) {
                $errors[] = 'Missing field '.$object_required_field;
            }
        }
        if (empty($errors)) {
            $object = new $object_name();
            foreach ($fields as $field_name => $value) {
                if (in_array($field_name, array('firstname', 'lastname'))) {
                    $value = preg_replace('/^[0-9!<>,;?=+()@#"°{}_$%:]*$/u', '', $value);
                }
                if (isset($object_name::$definition['fields'][$field_name]['size'])) {
                    $value = Tools::substr($value, 0, $object_name::$definition['fields'][$field_name]['size']);
                }
                if (property_exists($object_name, $field_name)) {
                    $object->$field_name = $value;
                }
            }

            //Exception management
            if ($object_name == 'Customer') {
                if (version_compare(_PS_VERSION_, '1.5.4.0', '>=')) {
                    $object->id_lang = Configuration::get('PS_LANG_DEFAULT');
                }
                $password = Tools::passwdGen();

                if (version_compare(_PS_VERSION_, '1.7', '<')) {
                    $object->passwd = Tools::encrypt($password);
                } else {
                    $object->passwd = Tools::hash($password);
                }
            }

            try  {
                $object->add();
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        return array(
            'object' => $object,
            'errors' => $errors,
        );
    }

    public function updateObject($object_name, $fields, $id_object_to_update)
    {
        $errors = array();
        $object = new $object_name($id_object_to_update);
        if (Validate::isLoadedObject($object)) {
            foreach ($fields as $field_name => $value) {
                if (property_exists($object_name, $field_name)) {
                    $object->$field_name = $value;
                }
            }
            try  {
                $object->update();
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        } else {
            $errors[] = 'Id not found';
        }

        return array(
            'object' => $object,
            'errors' => $errors,
        );
    }
}
