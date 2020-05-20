<?php
class ClientModel extends Model
{

    public function query($sql)
    {

        $result = array();
        if ($query = $this->db->query($sql)) {

            while ($row = mysqli_fetch_assoc($query)) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function getTravelData($salesRepId, $date)
    {
        $sql = " select * from travel where sales_rep_id=" . $salesRepId . " and DATE_FORMAT(traveldate,'%Y-%c-%e')=" . quote($date);
        $result = array();
        if ($rs = $this->db->query($sql)) {
            $result = mysqli_fetch_assoc($rs);

        }
        return $result;
    }
    public function saveTravelData($data)
    {
        $sql = "delete from travel where sales_rep_id =" . $data['sales_rep_id'] . " and DATE_FORMAT(traveldate,'%Y-%c-%e')=" . quote($data['traveldate']);
        $this->db->query($sql);

        if ($data['startkm'] && $data['endkm']) {
            $sql = insert_string('travel', $data);
            $this->db->query($sql);
        }

    }

    public function insert($data)
    {
        $sql = insert_string('clients', $data);
        $query = $this->db->query($sql);
    }

    public function updateOrInsert($data)
    {
        $salesRepId = 0;
        // get the salesrep id
        $sql = "select id from users where name=" . quote($data['salesrep']);
        if ($rs = $this->db->query($sql)) {
            $row = mysqli_fetch_assoc($rs);
            $salesRepId = $row['id'];
        }
        //pr($salesRepId);

        // search for record match by name
        $sql = "select client_id from clients where name=" . quote($data['name']);
        if ($rs = $this->db->query($sql)) {
            // record found
            $row = mysqli_fetch_assoc($rs);
            $data['client_id'] = $row['client_id'];
            $this->db->query(update_string("clients", $data));

            // insert into user_clients table
            if ($salesRepId) {
                $data['salesrep_id'] = $salesRepId;
                $this->db->query(insert_string('user_clients', $data));
            }
        }

    }

    public function updateUsingMYOBID($data)
    {
        // $sql = "update clients SET `sales_rating`=".$data['sales_rating']." where`myob_card_id`='".$data['myob_card_id']."' ";
        $sql = update_string('clients', $data, " where `myob_card_id`='" . $data['myob_card_id'] . "'");
        if (!$this->db->query($sql)) {
            echo "failed with mysob_card_id = " . $data['myob_card_id'] . "<br />";
        }

    }

    public function getSalesReps()
    {
        $sql = "select *  from users where role='rep'";
        $result = array();
        if ($query = $this->db->query($sql)) {

            while ($row = mysqli_fetch_assoc($query)) {
                $result[] = $row;
            }
        }
        return $result;
    }

    /**
     * @desc Get the list of type options available for the TypeId
     */
    public function listAll($salesRepId)
    {
        $sql = " select clients.*,DATE_FORMAT(contact_history.call_datetime ,'%d-%m-%Y') as last_contacted," .
            " DATE_FORMAT(Date_Add(contact_history.call_datetime, INTERVAL clients.call_interval DAY),'%d-%m-%Y') as contact_before, " .
            " sum(order_items.price)as sales " .
            " from clients " .
            " left join contact_history on clients.client_id = contact_history.client_id " .
            " left join orders on clients.client_id = orders.client_id " .
            " left join order_items on orders.order_id = order_items.order_id " .
            " left join user_clients on clients.client_id = user_clients.client_id";

        if (!empty($salesRepId)) {
            $sql .= " where users_clients.salesrep_id =" . $salesRepId;
        }
        $sql .= " GROUP BY clients.client_id";

        return $this->db->ajaxQuery($sql);
    }

    public function getRunsheet($salesRepId, $date)
    {
        $result = array();
        $sql = " select contact_history.*,clients.name as name,clients.contacts as contacts from contact_history " .
        " join clients on contact_history.client_id = clients.client_id" .
        " join user_clients on clients.client_id = user_clients.client_id" .
        " where user_clients.salesrep_id =" . $salesRepId . " and contact_history.call_datetime =" . quote($date) .
            " and contact_history.call_by=" . $salesRepId . ' order by contact_history.id asc';

        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_assoc($query)) {
                $result['data'][] = $row;
            }
        }

        return $result;

    }
    public function listClients($salesRepId, $query)
    {
        $cond = "";
        if (!empty($salesRepId)) {
            $cond = " and user_clients.salesrep_id = " . $salesRepId;
            $sql = " select * from clients " .
                " join user_clients on clients.client_id = user_clients.client_id " .
                " where clients.status ='active' and clients.name like '%" . $query . "%' " . $cond;
        } else {
            $sql = " select * from clients " .

                " where clients.status ='active' and clients.name like '%" . $query . "%' ";
        }

        return $this->db->ajaxQuery($sql);

    }

    public function deleteContactRecord($recId)
    {
        $recId = (int) $recId; // sanitise
        if ($recId > 0) {
            $this->db->query("delete from contact_history where id=" . $recId);
            return true;
        } else {
            return false;
        }

    }
    public function updateContactRecord($data)
    {
        if ($data['id']) {
            $sql = update_string('contact_history', $data);
            $this->db->query($sql);

        } else {
            // $this->addContactRecord($data);
        }
    }
    public function addContactRecord($data)
    {

        // Hack to stop the call_datetime being set unless the call type was a visit, email, or phone
        if (in_array(strtolower($data['call_type']), array('visit'))) {
            $data['last_contacted_datetime'] = $data['call_datetime'];
        }

        $sql = insert_string('contact_history', $data);
        //pr($sql);
        $this->db->query($sql);

    }
    public function updateClientContacts($data)
    {

        $sql = "update clients set contacts=" . quote($data['contacts']) . " where client_id=" . $data['client_id'];
        $this->db->query($sql);
    }

    public function getOrderListSummary($clientId)
    {
        $result = array();
        $sql = " select system_orders.id as id, Date_Format(system_orders.modified,'%m/%d/%Y') as date,system_orders.status as status, sum(system_order_items.qty * system_order_items.price) as value from system_orders " .
            " join system_order_items on system_orders.id = system_order_items.order_id " .
            " where system_orders.client_id=" . $clientId . " group by system_orders.id";
        $query = $this->db->query($sql);
        if ($query) {
            while ($row = mysqli_fetch_assoc($query)) {
                $result['data'][] = $row;
            }
        }

        return $result;

    }

    /**
     * @desc Calc the avg sale, call frequency and the conversion rate
     */
    public function repSalesOrders($days = 30)
    {
        $sql = " select sum(system_order_items.qty * system_order_items.price)" .
            " as sales,salesrep_id" .
            " from system_orders " .
            " left join user_clients on system_orders.client_id = user_clients.client_id" .
            " join users on users.id = user_clients.salesrep_id" .
            " join system_order_items on system_order_items.order_id = system_orders.order_id" .
            " where (TO_DAYS(NOW()) - TO_DAYS(modified)) < " . $days .
            " Group by user_clients.salesrep_id";
        //pr($sql);
        $result = array();
        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_assoc($query)) {
                $result[$row['salesrep_id']]['sales'] = $row['sales'];
            }
        }
        // get the number of orders
        $sql = "select
count(system_orders.id) as orders,salesrep_id
from system_orders
join user_clients on system_orders.client_id = user_clients.client_id
join users on users.id = user_clients.salesrep_id

where (TO_DAYS(NOW()) - TO_DAYS(modified)) < " . $days . "

Group by user_clients.salesrep_id";
        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_assoc($query)) {
                $result[$row['salesrep_id']]['orders'] = $row['orders'];
            }
        }

        // get calls made in period
        $sql = " select count(*) as calls,call_by from contact_history
                join call_type_options on call_type_options.id=contact_history.call_type_id
				where call_type_options.adjust_call_cycle = 1
				and (TO_DAYS(NOW()) - TO_DAYS(call_datetime)) < " . $days . "
				Group by call_by";

        //pr($sql);

        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_assoc($query)) {
                $result[$row['call_by']]['calls'] = $row['calls'];
            }
        }

        return $result;

    }

    /**
     * @desc Calc the avg sale, call frequency and the conversion rate
     * for a given date range
     */
    public function repSalesOrdersRange($startDate, $endDate)
    {
        $sql = " select sum(system_order_items.qty * system_order_items.price)" .
            " as sales,salesrep_id" .
            " from system_orders " .
            " left join user_clients on system_orders.client_id = user_clients.client_id" .
            " join users on users.id = user_clients.salesrep_id" .
            " join system_order_items on system_order_items.order_id = system_orders.order_id" .
            " where modified between '" . $startDate . "' and '" . $endDate . "'" .
            " Group by user_clients.salesrep_id";
        //pr($sql);
        $result = array();
        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_assoc($query)) {
                $result[$row['salesrep_id']]['sales'] = $row['sales'];
            }
        }
        // get the number of orders
        $sql = "select
count(system_orders.id) as orders,salesrep_id
from system_orders
join user_clients on system_orders.client_id = user_clients.client_id
join users on users.id = user_clients.salesrep_id

 where modified between '" . $startDate . "' and '" . $endDate . "'

Group by user_clients.salesrep_id";
        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_assoc($query)) {
                $result[$row['salesrep_id']]['orders'] = $row['orders'];
            }
        }

        // get calls made in period
        $sql = "select count(*) as calls,call_by from contact_history
                join call_type_options on call_type_options.id=contact_history.call_type_id
                where call_type_options.adjust_call_cycle = 1
                AND `call_datetime` between '" . $startDate . "' and '" . $endDate . "'
                Group by call_by";

        // pr($sql);

        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_assoc($query)) {
                $result[$row['call_by']]['calls'] = $row['calls'];
            }
        }

        return $result;

    }

    /**
     * @desc Planner stuff below
     */
    public function callReport($salesRepId)
    {
        if ($salesRepId) {
            $where = " where users.id =" . $salesRepId;
            // $where .= " and contact_history.call_type IN ('visit','email','phone')";
        } else {
            $where = "";
        }

        $sql = " select clients.client_id,clients.name,clients.phone_area_code,clients.phone,clients.contacts,clients.call_frequency,clients.level, clients.call_planning_note," .
        " max(contact_history.last_contacted_datetime) as lastcall," .
        //" min(contact_history.call_datetime) as firstcall,".
        //" count(contact_history.call_datetime) as calls, ".
        " max(system_orders.modified) as lastorderdate " .
            " from clients " .
            " left join contact_history on clients.client_id = contact_history.client_id  " .
            " join users on users.id = clients.salesrep " .
            " left join system_orders on clients.client_id = system_orders.client_id" .
            $where .
            " group By clients.client_id " .
            " order by clients.call_frequency asc";
        // echo $sql;

        $result = array();
        if ($query = $this->db->query($sql)) {
            if ($query == -1) {
                pr($sql);
                return [];
            }
            while ($row = mysqli_fetch_assoc($query)) {
                if ($row['lastcall']) {
                    $row['duein'] = round($row['call_frequency'] - (time() - strtotime($row['lastcall'])) / (86400));
                } elseif ($row['call_frequency']) {
                    $row['duein'] = $row['call_frequency'];
                }
                $res[] = $row;

            }
            // sort by call frequency asc and duein asc
            foreach ($res as $key => $row) {
                $frequency[$key] = $row['call_frequency'];
                $duein[$key] = $row['duein'];
            }
            //array_multisort($frequency, SORT_ASC,$duein, SORT_ASC,$res);
            array_multisort($duein, SORT_ASC, $res);
            $result['data'] = $res;
        }
        return $result;

    }
    public function get($id)
    {
        if ($id) {
            $sql = 'select clients.*,users.name as salesrep_name from clients left join user_clients on user_clients.client_id = clients.client_id left join users on users.id = user_clients.salesrep_id where clients.client_id=' . $id;

            $result = $this->db->fetchRow($sql);
            if ($this->db->error) {
                flashError($this->db->lastQuery);
            }
            return $result;
        }
    }

}
