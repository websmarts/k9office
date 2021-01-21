<?php
class AdminController extends Controller
{

	var $Client;  // ClientModel
	function beforeAction()
	{

		if (!isset($_SESSION['PASS'])) {
			header('Location: /');
			exit;
		}
		// load any models we need for this controller
		$this->Client = $this->_loadModel('client');
	}
	function index()
	{
	}
	/**
	 * @desc administer products
	 * This loads the products admin page that uses heaps of ajax calls to ProductController
	 */
	function products()
	{
		$this->layout = "ajax_admin";
	}
	/**
	 * @desc  administer clients
	 */
	function clients()
	{
		if ($this->data) {
			$this->set('clients', $this->Client->listClients($this->data['q']));
		}
	}
	function editclient()
	{
		if ($this->data) {
			$this->Client->updateClient($this->data);
			flashMessage('client updated');
			$this->returnToReferer();
			exit;
		} else {
			$id = $this->R->uriSegments[3];
			$this->set('client', $this->Client->getClient($id));
			$this->set('salesreps', $this->Client->getSalesReps());
		}
	}

	function callplanner()
	{
		$this->layout = "ajax_admin";
	}
	function runsheet()
	{
		$this->set('salesreps', $this->Client->getSalesReps());
		$this->set('user', $_SESSION['PASS']['user']);
		$this->layout = "ajax_admin";
	}


	function orderedit()
	{
	}

	function x_util()
	{
		$filepath = "material/file1.txt";
		// read in the data file
		$lines = file($filepath);

		$salesreps['W'] = 'kerry';
		$salesreps['X'] = 'k9';
		$salesreps['Y'] = 'mike';
		$salesreps['Z'] = 'peta';

		$headerDone = false;
		foreach ($lines as $l) {
			if (!$headerDone) {
				$header = $l;
				$headerDone = true;
				continue;
			}
			$f = split("\t", $l);

			// phone code
			if (preg_match('/^\d\d \d+/', $m)) {
				pr($m);
			}


			$data = array(
				'name' => $f[0],
				'status' => 'active',
				'address1' => $f[2],
				'address2' => $f[3],
				'city' => $f[6],
				'state' => $f[7],
				'postcode' => $f[8],
				'phone_area_code' => $pac,
				'phone' => $f[1],
				//'mobile' =>$f[1],
				//'fax' =>$f[1],
				//'contacts' =>$f[1],
				//'call_interval' =>$f[1],
				//'alert' =>$f[1],
				'salesrep' => $salesreps[strtoupper(substr(rtrim($f[10]), -1))],
				'myob_card_id' => $f[1],
				'client_type' => substr($f[10], 1, 1),
				'sales_rating' => 0

			);

			//pr($data) ;

			$rs = $this->Client->updateOrInsert($data);

			// find the record by name
			$sql = "select client_id from clients where `name` ='" . addslashes($data['name']) . "'";
			//pr($sql);
			$result = $this->Client->query($sql);
			$c = count($result);
			if ($c != 1) {
				echo $c . "-" . $data['name'] . '<br>' . "\n";
			}
			$r[$c]++;

			$n++;
			if ($n > 1000) {
				break;
			}
		}
		pr($r);
		exit;
	}
	function util()
	{
		//get client sales
		$filepath = "material/file2.txt";
		// read in the data file
		$lines = file($filepath);

		$headerDone = false;
		foreach ($lines as $l) {
			if (!$headerDone) {
				$header = $l;
				$headerDone = true;
				continue;
			}
			$f = split("\t", $l);


			$sales = (int) preg_replace('/\,/', '', trim($f[2], '"$')) * 100;


			if ($sales > 600000) {
				$callFrequency = 7;
			} elseif ($sales > 200000) {
				$callFrequency = 30;
			} elseif ($sales > 50000) {
				$callFrequency = 40;
			} elseif ($sales > 1) {
				$callFrequency = 90;
			} else {
				$callFrequency = 180;
			}

			$data = array(
				'myob_card_id' => $f[4],
				'sales_rating' => $sales,
				'call_frequency' => $callFrequency

			);

			//pr($data) ;
			// exit;

			$rs = $this->Client->updateUsingMYOBID($data);
		}
		echo "done";
		exit;
	}

	function fix()
	{
		$clients = $this->Client->fix1();
		foreach ($clients as $c) {
			$this->Client->fix2($c);
		}
	}

	function nostock()
	{
		$sql = "select * from products where status ='active' and qty_instock < 1 order by product_code asc";
		$res = $this->db->fetchRows($sql);
		$this->set('result', $res);
	}

	function export()
	{
		$table = $this->R->requestSegment(3);
		if ($table) {

			// may need a table join so check if it is a view being asked for
			if ($table == 'clientprices') {
				$sql = '    select cp.*,c.name 
                            from client_prices as cp 
                            join clients as c on c.client_id = cp.client_id
                            ';
			} else {
				$sql = 'select * from ' . $table;
			}


			$res = $this->db->fetchRows($sql);
			$doHeader = true;



			foreach ($res as $row) {
				$line = '';
				foreach ($row as $key => $value) {
					//pr($row); exit;

					// remove and newlines or tabs embedded in the content
					$value = trim(preg_replace('/[\t\n\r]+/', '', $value));

					if ($doHeader) {
						if ($key == 'xxid') {
							$key = "_id";
						}
						$header .= $key . "\t";
					}
					if (!isset($value) || $value == "") {
						$value = "\t";
					} else {
						# important to escape any quotes to preserve them in the data.
						$value = str_replace('"', '""', $value);
						# needed to encapsulate data in quotes because some data might be multi line.
						# the good news is that numbers remain numbers in Excel even though quoted.
						$value = '"' . $value . '"' . "\t";
					}
					$line .= $value;
				}
				if ($doHeader) {
					$header .= 'ean_barcode' . "\t";
				} else {
					$line .= '"' .$row['barcode']. $this->checkdigit($row['barcode']) . '"' . "\t";
				}
				$doHeader = false;
				$data .= trim($line) . "\n";
			}

			if ($_GET['for'] == 'mc') {
				$this->exportMailChimpList($header, $data);
			}

			# this line is needed because returns embedded in the data have "\r"
			# and this looks like a "box character" in Excel
			$data = str_replace("\r", "", $data);
			# Nice to let someone know that the search came up empty.
			# Otherwise only the column name headers will be output to Excel.
			if ($data == "") {
				$data = "\nno matching records found\n";
			}
			// pr($header.$data);exit;
			$xlsdata = $header . "\n" . $data;

			$filename = $table . "_export_" . time() . ".xls";
			//header("Content-type: application/octet-stream");
			header("Content-Type: application/vnd.ms-excel; name='excel'");
			header("Content-Disposition: attachment; filename=" . $filename);
			header("Cache-Control: public");
			header("Content-length: " . strlen($xlsdata));
			echo $xlsdata;
			exit;
		}
	}
	function exportMailChimpList($header, $data)
	{


		//pr($data); exit;
		$keys = ['email_1', 'email_2', 'email_3', 'login_user'];
		$fieldIndexes = [];
		$headerArray = explode("\t", trim($header));


		$emailsFound = [];

		foreach ($headerArray as $k => $v) {
			if (in_array($v, $keys)) {
				$fieldIndexes[] = $k;
			}
		}

		$mcdata = []; // mailchimp data
		foreach (explode("\n", $data) as $row) {

			foreach ($fieldIndexes as $i) {
				$fields = explode("\t", $row);
				if (!empty($fields[$i])) {
					if (!in_array($fields[$i], $emailsFound)) {

						$emailsFound[] = $fields[$i]; // add the new email

						$fields[] = $fields[$i];

						$mcdata[] = implode("\t", $fields);
					}
				}
			}
		}

		// pr($mcdata);

		$headerArray[] = 'email';
		$header = implode("\t", $headerArray) . "\n";
		$data = '';
		foreach ($mcdata as $row) {
			$data .= $row . "\n";
		}

		$xlsdata = $header . "\n" . $data;

		$filename = "Mailchimp_export_" . time() . ".xls";
		//header("Content-type: application/octet-stream");
		header("Content-Type: application/vnd.ms-excel; name='excel'");
		header("Content-Disposition: attachment; filename=" . $filename);
		header("Cache-Control: public");
		header("Content-length: " . strlen($xlsdata));
		echo $xlsdata;
		exit;
	}
	function importCosts()
	{
		$filepath = "material/costs_20081111.txt";
		$n = 0;
		// read in the data file
		$lines = file($filepath);
		foreach ($lines as $l) {

			list($product_code, $status, $cost, $costed_date) = split("\t", $l);
			$cost = $cost * 100; // convert to cents
			$product_code = trim($product_code);

			$last_costed_date = date('Y-m-d', strtotime($costed_date));

			if (!empty($product_code)) {
				$sql = "UPDATE products set `cost`=$cost,`last_costed_date`='$last_costed_date' where `product_code` ='$product_code'";
				//pr($sql); exit;
				$this->db->query($sql);
				$n++;
			}
		}
		echo "updated $n records";
		exit;
	}

	
	function checkdigit( $digits)
	{
		if(!$digits){
			return '';
		}	
		
		$digits = (string) $digits;
		
		if(strlen($digits) != 12){
			return '';
		}
		
		// 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
		$even_sum = $digits[1] + $digits[3] + $digits[5] + $digits[7] + $digits[9] + $digits[11];
		// 2. Multiply this result by 3.
		$even_sum_three = $even_sum * 3;
		// 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
		$odd_sum = $digits[0] + $digits[2] + $digits[4] + $digits[6] + $digits[8] + $digits[10];
		// 4. Sum the results of steps 2 and 3.
		$total_sum = $even_sum_three + $odd_sum;
		// 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
		$next_ten = (ceil($total_sum / 10)) * 10;
		$check_digit = $next_ten - $total_sum;

		return $check_digit;
	}


	// END OF CLASS
}
