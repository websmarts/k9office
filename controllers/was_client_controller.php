<?php
class ClientController
    extends Controller
    {
    var $Client; // ClientModel

    function beforeAction()
        {
        if (!isSet($_SESSION['PASS']))
            {
            header('Location: /');
            exit;
            }
        // load any models we need for this controller
        $this->Client=$this->_loadModel('client');
        }

    function listAll()
        {
        if (isSet($this->data['salesrep']))
            {
            $salesRep=$this->data['salesrep'];
            }
        else
            {
            $salesRep='';
            }
        $result=$this->Client->listAll($salesRep);
        $this->ajaxReply($result);
        exit;
        }

    function edit()
        {
        $id=$this->R->requestSegment(3);

        if ($this->data)
            {
            // pr($this->data);
            // check if DELETE button clicked
            if (strtolower($this->data['b']) == 'delete' && $this->data['client_id'])
                {
                // delete the client
                $this->db->query('delete from clients where client_id=' . $this->data['client_id']);

                // delete from reps list
                $this->db->query('delete from user_clients where client_id=' . $this->data['client_id']);

                // system orders??

                }
            elseif (strtolower($this->data['b']) == 'update' && $this->data['client_id'])
                {
                // update the client record
                // pr($this->data);

                $where=" where client_id=" . $this->data['client_id'];
                $sql=update_string('clients', $this->data, $where);
                //pr($sql);
                $this->db->query($sql);

                // delete from users_clients and then add
                $this->db->query('delete from user_clients where client_id=' . $this->data['client_id']);
                // insert into user_clients
                $data['client_id']=$this->data['client_id'];
                $data['salesrep_id']=$this->data['salesrep'];
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
                'value' => 'active'
                ),
            1 => array
                (
                'key' => 'inactive',
                'value' => 'inactive'
                ),
            2 => array
                (
                'key' => 'pending_activation',
                'value' => 'pending_activation'
                )
            ));
        }

    function add()
        {
        if ($this->data)
            {
            // update the client record
            //pr($this->data);

            //pesky ints
            $this->data['myob_record_id']=isSet($this->data['myob_record_id']) ? (int)$this->data['myob_record_id'] : 0;
            $this->data['call_frequency']=isSet($this->data['call_frequency']) ? (int)$this->data['call_frequency'] : 0;

            $sql=insert_string('clients', $this->data, $where);
            //pr($sql);
            //exit;
            $clientId=$this->db->query($sql);

            // insert into user clients
            if ($this->data['salesrep'] > 0 && $clientId)
                {
                $data['client_id']=$clientId;
                $data['salesrep_id']=$this->data['salesrep'];
                $this->db->query(insert_string('user_clients', $data));
                }
            }
        $this->set('online_status', array
            (
            0 => array
                (
                'key' => 'active',
                'value' => 'active'
                ),
            1 => array
                (
                'key' => 'inactive',
                'value' => 'inactive'
                ),
            2 => array
                (
                'key' => 'pending_activation',
                'value' => 'pending_activation'
                )
            ));

        $this->set('salesrep', $this->Client->getSalesReps());
        }

    function orderhistory()
        {
        $clientId=$this->R->uriSegments[3];
        $sortBy=$this->R->uriSegments[4];

        if (!empty($sortBy))
            {
            $orderBy=" order by product_code asc";
            }
        else
            {
            $orderBy=" order by tqty desc";
            }

        if ($clientId)
            {
            $sql=
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
                    and DATE_SUB(NOW(),INTERVAL 12 MONTH) < orders.modified
                    group by product_code ";
            $sql.=$orderBy;

            $this->set('result', $this->db->fetchRows($sql));
            $this->set('client',
                $this->db->fetchRow("select client_id,name from clients where client_id=" . $clientId));
            }
        }

    /**
    * Count the stock the client has in store
    * 
    */
    function stockcount()
        {
        $clientId=(int)$this->R->uriSegments[3];
        $sortBy=$this->R->uriSegments[4];
        $repId=$_SESSION['S']->id;

        if ($this->data && $clientId && $repId)
            {
            // save data to clientstock table
            $sql='delete from clientstock where DATE(`datetime`)=DATE(NOW()) and client_id=' . $clientId
                . ' and user_id=' . $repId;
            $this->db->query($sql);

            foreach ($this->data['instock'] as $productCode => $qty)
                {
                if (strlen($qty))
                    {
                    $qty=(int)$qty;
                    $suggestedOrderQty=(int)$this->data['order'][$productCode];
                    $sql=
                        'INSERT into clientstock (client_id,product_code,stock_count,suggested_order_qty,datetime,user_id) VALUES ('
                        . $clientId . ',"' . $productCode . '",' . $qty . ',' . $suggestedOrderQty . ',"'
                        . date('Y-m-d H:i') . '",' . $repId . ')';
                    //$this->pr( $sql,1);
                    $this->db->query($sql);

                    // Update basket with suggested order qty
                    if ($suggestedOrderQty > 0)
                        {
                        $_SESSION['S']->basket[$productCode]=$suggestedOrderQty;
                        }
                    else
                        {
                        unset($_SESSION['S']->basket[$productCode]);
                        }
                    }
                }

/*
foreach($this->data['order'] as $productCode => $qty){
    $qty = (int) $qty;
    $sql = 'UPDATE clientstock set suggested_order_qty='.$qty .' where DATE(`datetime`)=DATE(NOW()) and user_id='.$repId .' and product_code="'.$productCode.'"';
    
    if($qty > 0){
        $_SESSION['S']->basket[$productCode]=$qty;
    } else {
        unset($_SESSION['S']->basket[$productCode]);
    }
    $this->db->query($sql);
}
*/

            }

        if (!empty($sortBy))
            {
            $orderBy=" order by product_code asc";
            }
        else
            {
            $orderBy=" order by tqty desc";
            }

        if ($clientId)
            {
            $sql=
                "select  
                    orders.order_id,
                    items.product_code, 
                    products.description as description,
                    products.size,
                    products.color_name,
                    products.can_backorder,
                    products.qty_instock,
                    type.name as type, 
                    sum(qty)as tqty,
                    orders.client_id 
                    from 
                    system_order_items as items
                    join system_orders as orders on items.order_id = orders.order_id
                    join products on items.product_code = products.product_code
                    join `type` on products.typeid = type.typeid 
                    where orders.client_id =$clientId     
                    and DATE_SUB(NOW(),INTERVAL 12 MONTH) < orders.modified
                    and products.status = 'active' 
                    and (products.qty_instock > 0 || products.can_backorder > 0) 
                    group by product_code ";
            $sql.=$orderBy;
//echo $sql;
            $items=$this->db->fetchRows($sql);

            if (is_array($items) && count($items))
                {
                foreach ($items as $k => $item)
                    {
                    // check if BOM
                    $bom_items=$this->db->fetchRows("select * from boms where parent_product_code='" . $item['product_code']. "'");
 //                   pr($item['product_code']);
                    if(count($bom_items)){ // item is a BOM
                        $max_available = $this->isBomAvailable($bom_items);

                        if (!$max_available)
                            {
                            unset($items[$k]); //remove the item
                            }
                        else
                            {
                            $items[$k]['qty_instock']=$max_available;
                            }
                        }
                    }
                    
                }
            // sift through items and if Bom check availability

            $this->set('result', $items);

            // get clientstock data - stock count
            $sql=" select * from clientstock where client_id=$clientId and DATE(`datetime`)=DATE(NOW())";
            $clientstock=$this->db->fetchRows($sql, 'product_code');
            $basket=$_SESSION['S']->basket;


            //pr($basket);
            // merge basket and clientstock // basket wins
            if (is_array($basket) && count($basket))
                {
                foreach ($basket as $productCode => $qty)
                    {
                    $clientstock[$productCode]['product_code'] = $productCode;
                    $clientstock[$productCode]['suggested_order_qty']=$qty;
                    }
                }

            // pr($clientstock);
            $this->set('clientstock', $clientstock);

            $this->set('client',
                $this->db->fetchRow("select client_id,name from clients where client_id=" . $clientId));
            }
        }

    function isBomAvailable($bom_items)
        {
            
        $numbons=array();
        
//pr($bom_items);
        if (is_array($bom_items) && count($bom_items))
            { // it is a bom
            foreach ($bom_items as $item)
                {
                $res2 = $this->db->fetchRow('select qty_instock from products where product_code="'
                    . $item['item_product_code'] . '" and `status` != "inactive" ');

                // echo dumper($res2);
                if ($res2)
                    {
                    if ($item['item_qty'] > 0)
                        {
                        if ($res2['qty_instock'] != 0)
                            {
                            $numboms[]=(int)($res2['qty_instock'] / $item['item_qty']);
                            }
                        else
                            {
                            $numboms[]=0;
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
        function notifies(){
            $sql=
                "select 
                    notify_me.id as record_id, 
                    clients.name as clientname,
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
            $items=$this->db->fetchRows($sql);
            $this->set('result',$items);
        }
        
        /**
        * remove a notify me record and any similar , ie same client_id/product_id 
        */
        function notify_delete()
        {
            $recId=  $this->R->uriSegments[3];
            if($recId > 0){
                $res = $this->db->fetchRecordById('notify_me',$recId);
                // delete all other records with te same client Id and Product_code
                $sql = 'delete from notify_me where client_id='.$res['client_id'] .' and product_code="'.$res['product_code'].'"';
                
                $res2 = $this->db->query($sql);
                if($this->db->error){
                    pr($sql);
                    pr($this->db-error);
                    exit;
                }
            
            }
            $this->redirect('client/notifies/'); // exits
            
            
        }
        
        /**
        * let K9 know what baskets are in the system and who has them
        * 
        */
        function order_query()
        {
            $orderId = 0;
            if($_POST){
                $orderId = $_POST['order_id'];
            }
            
           
           //echo $orderId;
            
            if($orderId > 0){
                $sql = 'SELECT 
                        so.id AS order_id, 
                        c.name AS clientname, 
                        u.name AS k9user,
                        so.status AS orderstatus,
                        so.modified
                        FROM system_orders AS so 
                        LEFT JOIN clients AS c ON so.client_id=c.client_id 
                        LEFT JOIN users AS u ON so.reference_id = u.id 
                        WHERE so.id='.$orderId;
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
        
        function order_delete()
        {
            $orderId = (int) $this->R->uriSegments[3];
           
            //echo 'orderId='.$orderId;
            if($orderId > 0){
                $sql = 'DELETE from system_order_items where order_id= "T0_'.$orderId.'"';
                 //pr($sql) ;
                $this->db->query($sql);
                
                $sql = ' DELETE from system_orders where id='.$orderId .' LIMIT 1';
                //pr($sql) ;
                $this->db->query($sql);
            
                
            }
            
            $this->redirect('client/order_query');
        }
    }
?>