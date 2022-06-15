<?php
class ClientController extends Controller
{
    public $Client; // ClientModel

    public function beforeAction()
    {

        if (!isset($_SESSION['PASS'])) {
            header('Location: /');
            exit;
        }
        // load any models we need for this controller
        $this->Client = $this->_loadModel('client');
    }

    public function contact()
    {
        $id = $this->R->requestSegment(3);

        if ($_POST) {
            //$this->edit();
        }

        $this->set('formdata', $this->Client->get($id));

    }

    public function listAll()
    {
        if (isset($this->data['salesrep'])) {
            $salesRep = $this->data['salesrep'];
        } else {
            $salesRep = '';
        }
        $result = $this->Client->listAll($salesRep);
        $this->ajaxReply($result);
        exit;
    }

    public function edit()
    {
        $id = $this->R->requestSegment(3);

        if ($this->data) {
            // pr($this->data);
            // check if DELETE button clicked
            if (strtolower($this->data['b']) == 'delete' && $this->data['client_id']) {
                // delete the client
                $this->db->query('delete from clients where client_id=' . $this->data['client_id']);

                // delete from reps list
                $this->db->query('delete from user_clients where client_id=' . $this->data['client_id']);

                // system orders??

            } elseif (strtolower($this->data['b']) == 'update' && $this->data['client_id']) {
                // update the client record
                // pr($this->data);

                // pesky ints

                $this->data['myob_record_id'] = (int) $this->data['myob_record_id'];
                $this->data['call_frequency'] = (int) $this->data['call_frequency'];

                $where = " where client_id=" . $this->data['client_id'];
                $sql = update_string('clients', $this->data, $where);
                //pr($sql);exit;
                $this->db->query($sql);

                // delete from users_clients and then add
                $this->db->query('delete from user_clients where client_id=' . $this->data['client_id']);
                // insert into user_clients
                $data['client_id'] = $this->data['client_id'];
                $data['salesrep_id'] = $this->data['salesrep'];
                $this->db->query(insert_string('user_clients', $data));
            }
        }

        $this->set('formdata', $this->Client->get($id));
        $this->set('salesrep', $this->Client->getSalesReps());
        $this->set('online_status', array
            (
                0 => array
                (
                    'key' => 'active',
                    'value' => 'active',
                ),
                1 => array
                (
                    'key' => 'inactive',
                    'value' => 'inactive',
                ),
                2 => array
                (
                    'key' => 'pending_activation',
                    'value' => 'pending_activation',
                ),
            ));
        $this->set('state', array(
            0 => array(
                'key' => 'VIC',
                'value' => 'VIC',
            ),
            1 => array(
                'key' => 'NSW',
                'value' => 'NSW',
            ),
            2 => array(
                'key' => 'QLD',
                'value' => 'QLD',
            ),
            3 => array(
                'key' => 'NT',
                'value' => 'NT',
            ),
            4 => array(
                'key' => 'WA',
                'value' => 'WA',
            ),
            5 => array(
                'key' => 'SA',
                'value' => 'SA',
            ),
            6 => array(
                'key' => 'TAS',
                'value' => 'TAS',
            ),
            7 => array(
                'key' => 'ACT',
                'value' => 'ACT',
            ),
        ));

        $this->set('level', array(
            0 => array(
                'value' => 'AAA',
            ),
            1 => array(
                'value' => 'AA',
            ),
            2 => array(
                'value' => 'A',
            ),
            3 => array(
                'value' => 'B',
            ),
            4 => array(
                'value' => 'C',
            ),
            5 => array(
                'value' => 'D',
            ),
            6 => array(
                'value' => 'E',
            ),
            7 => array(
                'value' => 'F',
            ),
        ));

    }

    public function add()
    {
        if ($this->data) {
            // update the client record
            //pr($this->data);

            //pesky ints
            $this->data['myob_record_id'] = isset($this->data['myob_record_id']) ? (int) $this->data['myob_record_id'] : 0;
            $this->data['call_frequency'] = isset($this->data['call_frequency']) ? (int) $this->data['call_frequency'] : 0;

            $sql = insert_string('clients', $this->data, $where);
            //pr($sql);
            //exit;
            $clientId = $this->db->query($sql);

            // insert into user clients
            if ($this->data['salesrep'] > 0 && $clientId) {
                $data['client_id'] = $clientId;
                $data['salesrep_id'] = $this->data['salesrep'];
                $this->db->query(insert_string('user_clients', $data));
            }
        }
        $this->set('online_status', array
            (
                0 => array
                (
                    'key' => 'active',
                    'value' => 'active',
                ),
                1 => array
                (
                    'key' => 'inactive',
                    'value' => 'inactive',
                ),
                2 => array
                (
                    'key' => 'pending_activation',
                    'value' => 'pending_activation',
                ),
            ));

        $this->set('salesrep', $this->Client->getSalesReps());
    }

    public function orderhistory()
    {
        $clientId = $this->R->uriSegments[3];
        $sortBy = $this->R->uriSegments[4];

        if (!empty($sortBy)) {
            $orderBy = " order by product_code asc";
        } else {
            $orderBy = " order by tqty desc";
        }

        if ($clientId) {
            $sql =
                "select
            orders.order_id,
            items.product_code,
            products.description as description,
            type.name as type,
            sum(qty)as tqty,orders.client_id
            from
            system_order_items as items
            join system_orders as orders on items.order_id = orders.order_id
            join products on items.product_code = products.product_code
            join `type` on products.typeid = type.typeid
            where orders.client_id =$clientId
            and DATE_SUB(NOW(),INTERVAL 3 MONTH) < orders.modified
            group by orders.order_id,items.product_code ";
            $sql .= $orderBy;

            //$this->pr($sql);exit;

            $this->set('result', $this->db->fetchRows($sql));
            $this->set('client',
                $this->db->fetchRow("select client_id,name from clients where client_id=" . $clientId));
        }
    }

    /**
     * Count the stock the client has in store
     *
     */
    public function stockcount()
    {
        $clientId = (int) $this->R->uriSegments[3];
        $sortBy = $this->R->uriSegments[4];

        $months = (isSet($_GET['m']) && (int) $_GET['m'] > 0 && (int) $_GET['m'] < 25) ? (int) $_GET['m'] : 3;

        $repId = $_SESSION['S']->id;
        //$this->pr( $this->data,1);
        if ($this->data && $clientId && $repId) {
            // save data to clientstock table
            $sql = 'delete from clientstock where DATE(`datetime`)=DATE(NOW()) and client_id=' . $clientId
                . ' and user_id=' . $repId;
            $this->db->query($sql);

            foreach ($this->data['instock'] as $productCode => $instore_qty) {

                $qty = (int) $instore_qty;
                $suggestedOrderQty = (int) $this->data['order'][$productCode];
                if (strlen($instore_qty)) {
                    $sql =
                    'INSERT into clientstock (client_id,product_code,stock_count,suggested_order_qty,datetime,user_id) VALUES ('
                    . $clientId . ',"' . $productCode . '",' . $qty . ',' . $suggestedOrderQty . ',"'
                    . date('Y-m-d H:i') . '",' . $repId . ')';
                    //$this->pr( $sql,1);
                    $this->db->query($sql);

                    // Update basket with suggested order qty
                    //NOTE: Rep can ONLY update basket qty for items they have entered
                    // a valid Instore qty
                    if ($suggestedOrderQty > 0) {
                        // Need to check if product has enough  instock to do this
                        $_SESSION['S']->basket[$productCode] = $suggestedOrderQty;
                    } else {
                        unset($_SESSION['S']->basket[$productCode]);
                    }

                }

            }

           

        }

        if (!empty($sortBy)) {
            $orderBy = " order by product_code asc";
        } else {
            $orderBy = " order by tqty desc";
        }

        if ($clientId) {
            $sql =
                "select
            orders.order_id,
            items.product_code,
            products.description as description,
            products.size,
            products.color_name,
            products.can_backorder,
            products.qty_instock,
            products.notify_when_instock,
            type.name as type,
            sum(qty)as tqty,
            orders.client_id
            from
            system_order_items as items
            join system_orders as orders on items.order_id = orders.order_id
            join products on items.product_code = products.product_code
            join `type` on products.typeid = type.typeid
            where orders.client_id =$clientId
            and DATE_SUB(NOW(),INTERVAL " . $months ." MONTH) < orders.modified
            and products.status = 'active'
            group by orders.order_id,items.product_code ";
            $sql .= $orderBy;
            //echo pr($sql);exit;
            $items = $this->db->fetchRows($sql);
            

            if (is_array($items) && count($items)) {
                foreach ($items as $k=>$item) {
                    // check if BOM
                    $bom_items = $this->db->fetchRows("select * from boms where parent_product_code='" . $item['product_code'] . "'");
                    //                   pr($item['product_code']);
                    if (count($bom_items)) {
                        // item is a BOM
                        $max_available = $this->isBomAvailable($bom_items);

                        if (!$max_available) {
                            unset($items[$k]); //remove the item
                        } else {
                            $items[$k]['qty_instock'] = $max_available;
                        }

                        /*Make sure the bom items are shown as well as the BOM set
                        - ie client may reorder just the bom_items not the whole set
                         */
                        // foreach ($bom_items as $i) {
                        //     //pr($bom_items);
                        //     if (!isset($items[$i['item_product_code']])) {
                        //         // add in the bom items to stock list
                        //         $bom_item = $this->db->fetchRow('select * from products where product_code="' . $i['item_product_code'] . '"');
                        //         if ($bom_item['qty_instock'] > 0 || $bom_item['can_backorder'] > 0) {
                        //             $items[$i['item_product_code']] = $bom_item;
                        //         }
                        //     }
                        // }
                    }
                }

            }
            // sift through items and if Bom check availability

            foreach($items as $item){

                if( isSet($newItems[$item['product_code']]) ){
                    $item['tqty'] += $newItems[$item['product_code']]['tqty'];
                }
                $newItems[$item['product_code']] = $item;
            }

            

            //pr($newItems);exit;

            // sort items by product code or tqty
            // note tqty for bom_items maybe zero given client order BOM
            if (!empty($sortBy)) {
                 ksort($newItems);
            } else {
                // do nothing
            }

            // pr($newItems); exit;
            $this->set('result', $newItems);

            // get clientstock data - stock count
            $sql = " select * from clientstock where client_id=$clientId and DATE(`datetime`)=DATE(NOW())";
            $clientstock = $this->db->fetchRows($sql, 'product_code');
            $basket = $_SESSION['S']->basket;

            //pr($basket);
            // merge basket and clientstock // basket wins
            if (is_array($basket) && count($basket)) {
                foreach ($basket as $productCode => $qty) {
                    $clientstock[$productCode]['product_code'] = $productCode;
                    $clientstock[$productCode]['suggested_order_qty'] = $qty;
                }
            }

            // pr($clientstock);
            $this->set('clientstock', $clientstock);

            $this->set('client',
                $this->db->fetchRow("select client_id,name from clients where client_id=" . $clientId));

            //$this->set('last_orders', $this->clientsLastOrders($clientId,$months));
        }
    }

    public function clientsLastOrders($clientId,$months){

        // Get the last order
        $sql = "select * from system_orders where client_id=".$clientId." 
        and DATE_SUB(NOW(),INTERVAL " . $months ." MONTH) < system_orders.modified order by client_id desc limit 3";

        

        $lastOrders = $this->db->fetchRows($sql);



        $lastOrdersData=[];

        if($lastOrders){

            foreach($lastOrders as $lastOrder){
                $sql =
                "select
                orders.order_id,
                items.product_code,
                items.price,
                items.qty,
                products.description as description,
                products.size,
                products.color_name,

                type.name as typename
                
                from
                system_order_items as items
                join system_orders as orders on items.order_id = orders.order_id
                join products on items.product_code = products.product_code
                join `type` on products.typeid = type.typeid
                where orders.order_id = '".$lastOrder['order_id'] ."'" ;

                //pr($sql);

                $lastOrdersData[$lastOrder['order_id']]['order'] = $lastOrder;
                $lastOrdersData[$lastOrder['order_id']]['items'] = $this->db->fetchRows($sql);

            }
        }
        

        return $lastOrdersData;

    }

    public function isBomAvailable($bom_items)
    {

        $numbons = array();

        //pr($bom_items);
        if (is_array($bom_items) && count($bom_items)) {
            // it is a bom
            foreach ($bom_items as $item) {
                $res2 = $this->db->fetchRow('select qty_instock from products where product_code="'
                    . $item['item_product_code'] . '" and `status` != "inactive" ');

                // echo dumper($res2);
                if ($res2) {
                    if ($item['item_qty'] > 0) {
                        if ($res2['qty_instock'] != 0) {
                            $numboms[] = (int) ($res2['qty_instock'] / $item['item_qty']);
                        } else {
                            $numboms[] = 0;
                        }
                        //echo dumper($numboms);

                    }
                }
            }

            return min($numboms);
        }
    }
    /**
     * Mange client notifications
     *
     */
    public function notifies()
    {
        $sql =
            "select
        notify_me.id as record_id,
        clients.name as clientname,
        clients.email_1 as email1,
        clients.email_2 as email2,
        clients.email_3 as email3,
        users.name as k9_user,
        products.product_code,
        products.description,
        products.qty_instock

        from
        notify_me
        join clients on notify_me.client_id=clients.client_id
        left join users on notify_me.k9userID= users.id
        join products on notify_me.product_code = products.product_code
        order by clients.name asc";

        //echo $sql;
        $items = $this->db->fetchRows($sql);
        $this->set('result', $items);
    }

    /**
     * remove a notify me record and any similar , ie same client_id/product_id
     */
    public function notify_delete()
    {
        $recId = $this->R->uriSegments[3];
        if ($recId > 0) {
            $res = $this->db->fetchRecordById('notify_me', $recId);
            // delete all other records with te same client Id and Product_code
            $sql = 'delete from notify_me where client_id=' . $res['client_id'] . ' and product_code="' . $res['product_code'] . '"';

            $res2 = $this->db->query($sql);
            if ($this->db->error) {
                pr($sql);
                pr($this->db - error);
                exit;
            }

        }
        $this->redirect('client/notifies/'); // exits

    }

    /**
     * let K9 know what baskets are in the system and who has them
     *
     */
    public function order_query()
    {
        $orderId = 0;
        if ($_POST) {
            $orderId = $_POST['order_id'];
        }

        //echo $orderId;

        if ($orderId > 0) {
            $sql = 'SELECT
            so.id AS order_id,
            c.name AS clientname,
            u.name AS k9user,
            so.status AS orderstatus,
            so.modified
            FROM system_orders AS so
            LEFT JOIN clients AS c ON so.client_id=c.client_id
            LEFT JOIN users AS u ON so.reference_id = u.id
            WHERE so.id=' . $orderId;
            //echo $sql;

        } else {
            $sql = 'SELECT
            so.id AS order_id,
            c.name AS clientname,
            u.name AS k9user,
            so.status AS orderstatus,
            so.modified,
            sum(soi.qty) as item_count
            FROM system_orders AS so
            LEFT JOIN clients AS c ON so.client_id=c.client_id
            LEFT JOIN users AS u ON so.reference_id = u.id
            LEFT JOIN system_order_items AS soi ON so.order_id=soi.order_id
            WHERE so.`status`="basket"
            GROUP BY so.order_id';
        }
        $this->set('result', $this->db->fetchRows($sql));
    }

    public function order_delete()
    {
        $orderId = (int) $this->R->uriSegments[3];

        //echo 'orderId='.$orderId;
        if ($orderId > 0) {
            $sql = 'DELETE from system_order_items where order_id= "T0_' . $orderId . '"';
            //pr($sql) ;
            $this->db->query($sql);

            $sql = ' DELETE from system_orders where id=' . $orderId . ' LIMIT 1';
            //pr($sql) ;
            $this->db->query($sql);

        }

        $this->redirect('client/order_query');
    }
}
