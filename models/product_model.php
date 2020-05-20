<?php
class ProductModel extends Model
{

    public function find($key, $limit = 50)
    {
        $result = array();
        $sql = "select id,product_code,description,price,typeid from products where products.status !='inactive' and products.product_code like '" . $key . "%' limit " . $limit;
        return $this->db->ajaxQuery($sql);
    }
    public function newOrder($data)
    {
        return $this->db->query(insert_string('system_orders', $data));

    }

    public function makeOrderLineItem($data)
    {
        return $this->db->query(insert_string('system_order_items', $data));
    }

/**
 * @desc lookup Product Types
 */
    public function listTypes($clue = "", $limit = 0)
    {
        $result = array();
        if (!empty($clue)) {
            $where = " where name like '%" . $clue . "%'";
            if ($limit > 0) {
                $sqllimit = " LIMIT " . $limit;
            } else {
                $sqllimit = '';
            }
        } else {
            $where = " ";
        }
        $sql = "select * from type " . $where . " order by name asc " . $sqllimit;

        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_object($query)) {
                $result[] = $row;
            }
        }
        return array(
            'replyCode' => '200',
            'replyText' => 'Ok',
            'data' => $result,
        );
    }
/**
 * @desc get type record
 */
    public function getType($id)
    {
        $result = array();
        if ($id) {
            $sql = "select * from type where typeid=" . $id;
            if ($query = $this->db->query($sql)) {
                $result = mysqli_fetch_assoc($query);
            }
        }

        return $result;
    }
    public function updateType($data)
    {
        $sql = update_string('type', $data, " where typeid=" . $data['typeid']);
        $this->db->query($sql);
    }
    public function updateTypeOptions($data)
    {
        if (isset($data['typeid']) && $data['typeid']) {
            $this->db->query("delete from type_options where typeid=" . $data['typeid']);
            if (isset($data['display_format']) && $data['display_format'] == 'h') {
                if (isset($data['opt_code']) && count($data['opt_code']) > 0) {
                    foreach ($data['opt_code'] as $k => $optCode) {
                        if (!empty($optCode)) {
                            $optClass = isset($data['opt_class'][$k]) ? $data['opt_class'][$k] : '';
                            $sql = "INSERT into type_options (typeid,opt_code,opt_class) VALUES (" . $data['typeid'] . "," . quote($optCode) . "," . quote($optClass) . ")";
                            $this->db->query($sql);
                        }

                    }

                }

            }
        }
    }
    public function addType($data)
    {
        $this->db->query(insert_string('type', $data));
    }
    public function deleteType($typeId)
    {
        if ($typeId) {
            $this->db->query("update products set status='inactive',typeid=0 where typeid=" . $typeId);
            $this->db->query("delete from type_cataegory where typeid=" . $typeId);
            $this->db->query("delete from type_options where typeid=" . $typeId);
            $this->db->query("delete from type where typeid=" . $typeId);
        }

    }

/**
 * @desc lookup products with the typeid
 * returns Products list
 */
    public function listTypeProducts($typeId)
    {
        $result = array();
        $sql = "select * from products where typeid =" . $typeId;
        if ($typeId > 0) {
            return $this->db->ajaxQuery($sql);
        }
    }

    /**
     * @desc Get the list of type options available for the TypeId
     */
    public function listTypeOptions($typeId)
    {
        if ($typeId > 0) {
            $sql = "select * from type_options where typeid =" . $typeId;
            return $this->db->ajaxQuery($sql);
        }
    }

    public function updateTypeOption($data)
    {
        $sql = "update type_options set`" . $data['field'] . "` = '" . $data['newData'] . "' where typeid=" . $data['typeid'] . " and `" . $data['field'] . "` ='" . $data['oldData'] . "' limit 1";
        if ($data['typeid'] > 0 && $this->db->query($sql) == 1) {
            return array(
                'replyCode' => '200',
                'replyText' => 'Ok',
                'data' => '',
            );
        } else {
            if ($this->db->error) {
                return array(
                    'replyCode' => '500',
                    'replyText' => 'Fail: ' . $this->db->error . " sql=" . $sql,
                    'data' => '',
                );
            }
        }

    }

    /**
     * @desc Get the list of the Category options available for the TypeId
     */
    public function listTypeCategories($typeId)
    {
        if ($typeId > 0) {
            $sql = "select category.* from category join type_category on category.id = type_category.catid  where type_category.typeid =" . $typeId;
            return $this->db->ajaxQuery($sql);
        }
    }

    public function updateProduct($data)
    {
        $sql = update_string('products', $data);
        if ($data['id'] > 0 && $this->db->query($sql) == 1) {
            return array(
                'replyCode' => '200',
                'replyText' => 'Ok',
                'data' => '',
            );
        } else {
            if ($this->db->error) {
                return array(
                    'replyCode' => '500',
                    'replyText' => 'Fail: ' . $this->db->error,
                    'data' => '',
                );
            }
        }

    }

    public function catalog()
    {
        $result = array();

        $sql = "select * from category order by name asc";
        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_object($query)) {
                if ($row->id != 42 && $row->id != 62 && $row->id != 61  && $row->id != 60 ) {
                    // ignore Economy packs
                    $cats[$row->parent_id][] = $row;
                }
            }
        }
        ksort($cats);
        $result['categorys'] = $cats;

        $sql = "   select category.id as catid,products.product_code,products.description,products.size,products.barcode,products.color_name,products.color_background_color, products.typeid,type.name,type.type_description,type.display_format,type.aus_made
                from products
                join `type` on type.typeid = products.typeid
                join type_category on type_category.typeid = type.typeid
                join category on type_category.catid=category.id
                where products.status ='active' and clearance < 1
                and category.name NOT LIKE 'Hidden%'
                order by type.display_order desc, products.display_order desc, price asc ";
        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_object($query)) {
                $result['products'][$row->catid][$row->typeid][] = $row;
            }
        }
        $sql = "select * from type_options";
        if ($query = $this->db->query($sql)) {
            while ($row = mysqli_fetch_object($query)) {
                $result['options'][$row->typeid][] = $row;
            }
        }

        return $result;
    }

}
