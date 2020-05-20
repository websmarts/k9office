<?php
class ProductController extends Controller
{

    public $Product; // Property Model
    public $Person; // Person Model
    public $Organisation; // Organisation Model

    public function beforeAction()
    {
        //die('beforeAction');

        if (!isset($_SESSION['PASS']) || $_SESSION['PASS']['user'] != 6) {
            //header('Location: /');
            //exit;
        }
        //pr($_SESSION['PASS']);

        // load any models we need for this controller
        $this->Product = $this->_loadModel('product');

    }

    public function index()
    {
        // just display the home page

    }

    /**
     * @desc Used by the product autocompleter on the order sheet on runsheet
     */
    public function findProducts()
    {
        $this->ajaxReply($this->Product->find($this->R->get('query'), 50));
    }

    public function saveorder()
    {
        $data['client_id'] = $this->data['client_id']; // client ordering goods
        $data['user_id'] = $this->data['user_id']; // the sales rep entering the order
        $items = $this->data['json']; // items ordered

        // save the order
        $data['status'] = "saved";
        $data['order_id'] = $this->Product->newOrder($data);

        if ($data['order_id']) {
            // save line items
            foreach ($items as $line => $detail) {
                $data['product_code'] = $detail['product_code'];
                $data['qty'] = $detail['qty'];
                $data['price'] = $detail['price'];
                $this->Product->makeOrderLineItem($data);

            }

        }
        $this->ajaxReply();

    }

    /*
     * AJAX lookup CITIES for autocomplte
     */
    public function listProductTypes()
    {
        $this->ajaxReply($this->Product->listTypes($this->R->get('query'), 50));
    }
    /*
     * AJAX list all products with the same typeid
     */
    public function listTypeProducts()
    {
        if ($typeId = $this->R->post('typeId')) {
            $result = $this->Product->listTypeProducts($typeId);
            $this->ajaxReply($result);
        } else {
            $this->errorReport('no typeId supplied to ' . __FUNCTION__ . "in file " . __FILE__);
        }
    }

    /**
     * @desc list the Categories the product type belongs to
     */
    public function listTypeCategories()
    {
        if ($typeId = $this->R->post('typeId')) {
            $result = $this->Product->listTypeCategories($typeId);
            $this->ajaxReply($result);
        } else {
            $this->errorReport('no typeId supplied to ' . __FUNCTION__ . "in file " . __FILE__);
        }
    }

    /**
     * @desc list the Types Options
     */
    public function listTypeOptions()
    {
        if ($typeId = $this->R->post('typeId')) {
            $result = $this->Product->listTypeOptions($typeId);
            $this->ajaxReply($result);
        } else {
            $this->errorReport('no typeId supplied to ' . __FUNCTION__ . "in file " . __FILE__);
        }
    }
    /**
     * @desc change the values for a type option
     */
    public function updateTypeOption()
    {
        if (strlen($this->R->post('field')) < 2 && $this->R->post('typeid') < 1) {
            $result = array(
                'replyCode' => '500',
                'replyText' => 'Fail: poor submit data',
                'data' => '',
            );
            $this->ajaxReply($result);
        }

        $data['typeid'] = $this->R->post('typeid');
        $data['oldData'] = $this->R->post('oldData');
        $data['newData'] = $this->R->post('newData');
        $data['field'] = $this->R->post('field');

        $this->ajaxReply($this->Product->updateTypeOption($data));
    }

    public function updateProduct()
    {
        // validate
        if (strlen($this->R->post('field')) < 2 && $this->R->post('id') < 1) {
            $result = array(
                'replyCode' => '500',
                'replyText' => 'Fail: poor submit data',
                'data' => '',
            );
            $this->ajaxReply($result);
        }

        $data['id'] = $this->R->post('id');
        $data[$this->R->post('field')] = $this->R->post('newData');
        $this->ajaxReply($this->Product->updateProduct($data));
    }

    /**
     * @desc NON AJAX ROUTINES BELOW
     */
    public function types()
    {

        $typeId = 0;

        if ($this->data) {
            switch ($this->data['b']) {
                case 'edit':
                    $typeId = $this->data['selected_typeid'];
                    break;
                case 'delete':
                    if (isset($this->data['selected_typeid']) && $this->data['selected_typeid'] > 0) {
                        $this->Product->deleteType($this->data['selected_typeid']);
                    }
                    break;
                case 'add':
                    $data['name'] = $this->data['newtype'];
                    if (isset($this->data['new_display_format'])) {
                        $data['display_format'] = $this->data['new_display_format'];
                    }
                    $this->Product->addType($data);
                    break;
                case 'update':
                    $this->Product->updateType($this->data);
                    $this->Product->updateTypeOptions($this->data);
                    $typeId = $this->data['typeid'];
                    break;

            }
        }

        if ($typeId) {
            $this->set('typeform', $this->Product->getType($typeId));
            $this->set('typeoptions', $this->Product->listTypeOptions($typeId));
            $this->set('typeproducts', $this->Product->listTypeProducts($typeId));
            $this->set('typecategories', $this->Product->listTypeCategories($typeId));
        }
        $this->set('typeid', $typeId);
        $this->set('types', $this->Product->listTypes());
    }
    public function catalog()
    {
        if ($this->data) {
            $this->db->query('delete from catalog_pagemarkers');
            //pr($this->data);
            if (is_array($this->data['pagebreakmarker']) && count($this->data['pagebreakmarker']) > 0) {
                foreach ($this->data['pagebreakmarker'] as $pagenum => $typeid) {
                    $typeid = (int) $typeid;
                    if (!empty($typeid)) {
                        $sql = 'insert into catalog_pagemarkers set `pagenum`=' . $pagenum . ', `typeid`=' . $typeid;
                        //pr($sql);
                        $this->db->query($sql);
                    }
                }
            }

            $pagenum = substr($this->data['b'], 14);
            //pr($_SERVER);exit;
            $url = $_SERVER['REQUEST_URI'];
            if (isset($_GET['pager'])) {
                //$url .= '?pager=1';
                if (!empty($pagenum)) {
                    $x = $pagenum + 1;
                    $url .= '#page' . $x;
                }
            }
            header('location:' . $url);
            exit;
        }
        $res = $this->db->fetchRows('select * from catalog_pagemarkers');
        $pagebreakmarkers = array();
        if (is_array($res) && count($res)) {
            foreach ($res as $p) {
                $pagebreakmarkers[$p['pagenum']] = $p['typeid'];
            }
        }

        $this->set('pagebreakmarkers', $pagebreakmarkers);
        $this->set('data', $this->Product->catalog());
        $this->page = 'catalog';
        $this->layout = "ajax";
    }

}
