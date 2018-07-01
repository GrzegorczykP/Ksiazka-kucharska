<?php

class Account {
    private $login;
    private $password;
    private $hashPassword;
    private $rep_password;
    private $e_mail;

    public function __construct($login, $pwd, $r_pwd, $e_mail) {
        $this->login = $login;
        $this->password = $pwd;
        $this->rep_password = $r_pwd;
        $this->e_mail = $e_mail;
        $this->hashPassword = password_hash($pwd, PASSWORD_DEFAULT);
    }

    private function checkData() {
        require_once __DIR__ . '/../functions.php';
        $connection = connectDB();
        $sql = "SELECT * FROM users WHERE nick LIKE ?";
        $prep = $connection->prepare($sql);
        $prep->bind_param('s',$this->login);
        $prep->execute();
        $result = $prep->get_result();
        $prep->close();
        $connection->close();
        if (strlen($this->login)>24) return 'Nazwa użytkownika jest zbyt długa';
        if (strlen($this->login)<3) return 'Nazwa użytkownika jest zbyt krótka';
        if (!preg_match("/^[a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ0-9_ ]*$/",$this->login)) return "Nazwa użytkownika może się składać tylko z liter (także polskich), cyfr, spacji i znaków '_'";
        if ($result->num_rows>0) return "Nazwa użytkownika jest już zajęta";

        if (strlen($this->password)>24) return 'Hasło jest zbyt długie';
        if (strlen($this->password)<3) return 'Hasło jest zbyt krótkie';

        if ($this->password!=$this->rep_password) return 'Podane hasła są różne';

        if (!filter_var($this->e_mail, FILTER_VALIDATE_EMAIL)) return 'Podany e-mail jest nieprawidłowy';
    }

    public function register($connection) {
        if (strlen($err = $this->checkData()) > 0) {
            return $err;
        } else {
            $sql = "INSERT INTO users (nick, password, e_mail, account_type) value(?, ?, ?,'normal')";
            $prep = $connection->prepare($sql);
            $prep->bind_param('sss',$this->login,$this->hashPassword,$this->e_mail);
            $prep->execute();
            if ($prep->affected_rows < 1) return 'Rejestracja nie powiodła sie';
            $prep->close();

            return '';
        }
    }

}