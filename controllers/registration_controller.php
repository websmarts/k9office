<?php
class RegistrationController
    extends Controller
    {
    function index()
        {
        if ($this->data)
            {
            //$this->pr($this->data,1);

            // check if we have a valid quickregcode
            if (!empty($this->data['quickregcode']))
                {
                //$this->encodeQuckRegCode($this->data['quickregcode']);// remove

                // get the real record_id
                $recordID=$this->decodeQuickRegCode($this->data['quickregcode']);

                if ($recordID)
                    {
                    $sql="select * from clients where myob_record_id=" . $recordID;
                    $client=$this->db->fetchRow($sql);

                    if ($this->db->error)
                        {
                        $_SESSION['flash_error']='Error reading database for id=' . $recordID;
                        $this->returnToReferer();
                        }

                    $this->redirect('registration/quickregister/' . $this->data['quickregcode']);

                    //$_SESSION['flash_errod']='Quick Registration Code='.$recordID;
                    //$_SESSION['flash_errod'].='Client data='.$this->pr($client,0);
                    }
                else
                    {
                    $_SESSION['flash_error']='Invalid Code - ';
                    }
                }
            else
                {
                $_SESSION['flash_error']='Invalid Quick Registration Code';
                $this->returnToReferer();
                }
            }
        $this->page='register';

        $this->set('formDef', $formDef);
        $this->layout='registration';
        }

    function quickregister()
        {

        $validate=array
            (
            'contact_name' => VALID_NOT_EMPTY,
            'email' => VALID_EMAIL,
            'pass1' => VALID_PASSWORD
            );

        $fieldLabels=array
            (
            'contact_name' => 'Contact Name',
            'email' => 'Email',
            'pass1' => 'Password'
            );

        if ($this->data)
            {
            // check password length
            $_SESSION['formdata']=$this->data;

            $errors=$this->validateInput($validate);

            if (count($errors))
                {
                $msg='The following fields are not valid:';

                foreach ($errors as $ek => $ev)
                    {
                    $msg.='<br />' . $fieldLabels[$ek];
                    }
                $_SESSION['flash_error']=$msg;
                $this->returnToReferer();
                }
            

            // Check email address is not already in use
            $sql="select * from clients where login_user=" . $this->db->quote($this->data['email']);
            $user=$this->db->fetchRow($sql);

            if ($user)
                {
                $_SESSION['flash_error']='Error: ' . $this->data['email'] . ' is already in use.';
                $this->returnToReferer();
                }

            //$this->pr($this->data, 1);

            $recordID=$this->decodeQuickRegCode($this->data['quickregcode']);

            // check the account is not already registered
            $client=$this->db->fetchRow('select * from clients where record_id=' . $recordID);

            if (!empty($client['online_validation_key']))
                {
                $_SESSION['flash_error']='Error: This client is already registered';

                if ($client['online_status'] == 'pending_activation')
                    {
                    $_SESSION['flash_error'].='<br /> Account is waiting for activation';
                    }
                $this->returnToReferer();
                }


            // Save the new registration pending activation

            //Get the current client record
            $now=time();

            $data=array
                (
                'login_user' => $this->data['email'],
                'login_pass' => $this->data['pass1'],
                'online_validation_key' => $now,
                'online_contact' => $this->data['contact_name'],
                'online_status' => 'pending_activation'
                );

            $this->db->updateRecord('clients', $data, 'where myob_record_id=' . $recordID);

            if ($this->db->error)
                {
                $_SESSION['flash_error']='Error: Could not update client record';
                //$_SESSION['flash_error'] .= $this->db->error . ' <br /> Last query: '.$this->db->lastQuery;
                $this->returnToReferer();
                }
            else
                {
                // send activation email
                $message=
                    "Thank you for registering at www.k9homes.com.au website. \r\nTo complete the registration please cut and paste the following link into your browser. ";
                $message.="\n http://www2.k9homes.com.au/office/registration/activation/" . $now . "\n";
                $message
                    .="\n\n Or if you prefer you can go to http://www2.k9homes.com.au/office/registration/activation/ and then enter the activation code below into the field provided.\n";
                $message.="\nYour activation code is:  " . $now . "\n\n";

                $message.="The K9Homes Team";
                $subject="Activate you K9Homes account ";
                $to=$this->data['email'];

                $headers='From: webmaster@www.k9homes.com.au' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                if (!mail($to, $subject, $message, $headers))
                    {
                    $_SESSION['flash_error']='An unexpected error occured while trying to send activation email to '
                        . $this->data['email'] . ' - please contact our office for assistance';
                    }


                // send user to activation page
                $this->redirect('registration/activation/');
                }
            } // End of POST handler

        $recordID=$this->decodeQuickRegCode($this->R->requestSegment(3));
        $sql="select * from clients where myob_record_id=" . $recordID;
        $client=$this->db->fetchRow($sql);

        if ($this->db->error)
            {
            $_SESSION['flash_error']='Error reading database for id=' . $this->R->requestSegment(3);
            $this->redirect('registration/');
            }
            
        if(!$client){
             $_SESSION['flash_error']=$this->R->requestSegment(3). ' Is not a valid Quick Registration Code' ;
             $this->redirect('registration/');
        }
        
        $this->set('fieldLabels', $fieldLabels);
        $this->set('client', $client);
        $this->set('quickregcode', $this->R->requestSegment(3));
        $this->layout='registration';
        }

    function activation()
        {
        $activationCode=$this->R->requestSegment(3);                // check GET setting
        $activationCode=!empty($this->data['activation_code'])
            ? $this->data['activation_code'] : $activationCode; // check POST setting

        if (!empty($activationCode))
            {
            // check activation code
            $client=$this->db->fetchRow(
                'select * from clients where online_status="pending_activation" and online_validation_key='
                . $activationCode);

            if ($client)
                {
                // update status to active and then redirect them to home with a message to login
                $this->db->updateRecord('clients', array('online_status' => 'active'),
                    ' where client_id=' . $client['client_id']);
                $_SESSION['flash_message']='Your account has now been activated - please login';
                header('location: http://www.k9homes.com.au/');
                exit;
                }
            else
                {
                $_SESSION['flash_error']='Invalid activation request';
                $this->returnToReferer();
                }
            }

        $this->layout='registration';
        }

    function manual()
        {
        $validate=array
            (
            'contact_name' => VALID_NOT_EMPTY,
            'email' => VALID_EMAIL,
            'pass1' => VALID_PASSWORD
            );

        $fieldLabels=array
            (
            'contact_name' => 'Contact Name',
            'email' => 'Email',
            'pass1' => 'Password'
            );

        if ($this->data)
            {
            // check password length
            $_SESSION['formdata']=$this->data;

            $errors=$this->validateInput($validate);

            if (count($errors))
                {
                $msg='The following fields are not valid:';

                foreach ($errors as $ek => $ev)
                    {
                    $msg.='<br />' . $fieldLabels[$ek];
                    }
                $_SESSION['flash_error']=$msg;
                $this->returnToReferer();
                }

            // Check email address is not already in use
            $sql="select * from clients where login_user=" . $this->db->quote($this->data['email']);
            $user=$this->db->fetchRow($sql);

            if ($user)
                {
                $_SESSION['flash_error']='Error: ' . $this->data['email'] . ' is already in use.';
                $this->returnToReferer();
                }

            $this->pr($this->data, 1);

            

            // Save the new registration pending activation

            //Get the current client record
            $now=time();

            $data=array
                (
                'login_user' => $this->data['email'],
                'login_pass' => md5($this->data['pass1']),
                'online_validation_key' => $now,
                'online_contact' => $this->data['contact_name'],
                'online_status' => 'pending_activation'
                );

            $this->db->insertRecord('clients', $data);

            if ($this->db->error)
                {
                $_SESSION['flash_error']='Error: Could not update client record';
                //$_SESSION['flash_error'] .= $this->db->error . ' <br /> Last query: '.$this->db->lastQuery;
                $this->returnToReferer();
                }
            else
                {
                // send activation email
                $message=
                    "Thank you for registering at www.k9homes.com.au website. \r\nTo complete the registration please cut and paste the following link into your browser. ";
                $message.="\n http://www2.k9homes.com.au/office/registration/activation/" . $now . "\n";
                $message
                    .="\n\n Or if you prefer you can go to http://www2.k9homes.com.au/office/registration/activation/ and then enter the activation code below into the field provided.\n";
                $message.="\nYour activation code is:  " . $now . "\n\n";

                $message.="The K9Homes Team";
                $subject="Activate you K9Homes account ";
                $to=$this->data['email'];

                $headers='From: webmaster@www.k9homes.com.au' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                if (!mail($to, $subject, $message, $headers))
                    {
                    $_SESSION['flash_error']='An unexpected error occured while trying to send activation email to '
                        . $this->data['email'] . ' - please contact our office for assistance';
                    }


                // send user to activation page
                $this->redirect('registration/activation/');
                }
            } // End of POST handler

        $this->set('fieldLabels', $fieldLabels);
        $this->page='register_manual';
        $this->layout='registration';
        }

    function decodeQuickRegCode($code)
        {
        if (empty($code))
            {
            return 0;
            }
        $codeLength = strlen($code);
        $keyLength = $codeLength - 3;
        $key=substr($code, 1, $codeLength -3); // skip dummy first but take next four numbers
        $checkCode=(int)substr($code, -2);
        $fillerCode=(int)ord(strtoupper(substr($code, 0, 1)));

        for ($n=0;$n < $keyLength;$n++){
            $keyValue += (int)substr($key, $n, 1);
        }


        // check that checkcode is sum of all key numbers eg 1234 = 1+2+3+4= 10
        if ($fillerCode + $keyValue != $checkCode)
            {
            return 0;
            }
        else
            {
                return $key;
            }
                        
                    
               
            
            
        }

    function encodeQuckRegCode($n)
        {
        $str=(string)$n;
        $len=strlen($str);

        if ($len > 4)
            {
            $str=substr($str, 0, 4); // chop back to len = 4 max
            }

        if ($strlen < 4)
            {
            $str=str_pad($str, 4, '0', STR_PAD_LEFT);
            }

        echo $str;
        return $str;
        }
    }
?>