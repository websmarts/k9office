<?php

class State
{
    public $id;
    public $role;
    public $client_id;
    public $rep_name = ""; // if role = rep then this holds rep name - should add real name into table sometime
    public $newLogin = false; // flag to indicate just logged in
    public $req; // an array holding http req params
    public $basket;
    public $basket_instructions;
    public $lastview;
    public $nextview;
    public $db;

    public function __construct($db)
    {
        $this->db = $db;

    }

    public function setDB($db)
    {
        $this->db = $db;

    }

    public function authenticate($username, $password)
    {
        $qry = "SELECT * from `users` WHERE `name`='$username' AND md5('$password') = `pass` ";

        $result = $this->db->GetArray($qry);

        //echo dumper($result);

        if ($result[0]['name'] = $username) {
            $this->id = $result[0]['id'];
            $this->role = $result[0]['role'];
            $this->rep_name = $result[0]['name'];
            // okay to set the session vars
            //$_SESSION['user']['id'] =  $result[0]['id'];
            //$_SESSION['user']['role'] = $result[0]['role'];

            // if user role is not set then pick up their client_id for the user_clients table
            if ($result[0]['role'] == "client") {
                $r = $this->db->GetArray("select client_id from user_clients where id=" . $result[0]['id']);
                if ($r) {
                    $this->client_id = $r[0]['client_id'];
                    $this->newLogin = true;

                    // if client - restore basket
                    if ($this->role == "client") {
                        restore_client_basket();
                    }
                }
            }

        } else {
            unset($this->id);
            unset($this->role);
            unset($this->client_id);
            unset($this->basket);

        }
    }

    public function is_valid()
    {
        if (isset($_SESSION['user'])) {
            return true;
        } else {
            return false;
        }
    }

    public function logout()
    {
        if ($this->client_id) {
            save_basket("basket", $this->client_id);
        }
        unset($this->id, $this->role, $this->client_id, $this->basket, $this->rep_name); // kill user params to logout user

        $this->nextview = "default";
    }

    public function show_current_state()
    {

        $r = "<p>S->id=" . $this->id .
        "<br>S->role=" . $this->role .
        "<br>S->client_id=" . $this->client_id .
        "<br>count(S->basket)=" . count($this->basket) .
        "<br>S->lastview=" . $this->lastview .
        "<br>S->nextview=" . $this->nextview .
        "<br>S->newLogin=" . $this->newLogin .
        "<br>S->module=" . $this->module .
            "</p>";

        return $r;
    }

    public function getUserRole()
    {
        return $this->role;
    }

    public function clearBasket()
    {
        unset($this->basket);
        unset($this->basket_instructions);
    }

}
