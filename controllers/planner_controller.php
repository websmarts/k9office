<?php
class PlannerController extends Controller
{

    public $Client; // model

    public function beforeAction()
    {
        if (!isset($_SESSION['PASS'])) {
            header('Location: /');
            exit;
        }
        // load any models we need for this controller
        $this->Client = $this->_loadModel('client');

    }

    public function index()
    {
        //pr($_SESSION);
        $this->set('salesreps', $this->Client->getSalesReps());
        if ($this->R->requestSegment(3)) {
            $salesRepId = $this->R->uriSegments[3];
            $_SESSION['displayuserid'] = $salesRepId;
        } elseif ($_SESSION['displayuserid']) {
            $salesRepId = $_SESSION['displayuserid'];
        } elseif ($_SESSION['PASS']['user']) {
            $salesRepId = $_SESSION['PASS']['user'];
        }
        //pr($salesRepId);
        if ($salesRepId) {
            $this->set('results', $this->Client->callReport($salesRepId));
            $this->set('salesrep_id', $salesRepId);
        }

        // get sales rep sales and order figures
        $this->set('sales30', $this->Client->repSalesOrders(90));
        $this->set('sales7', $this->Client->repSalesOrders(7));

        // if date range posted then get the sales and order figures for that date range
        if ($_POST && $_POST['b']) {
            $startDate = $_POST['startdate'];
            $endDate = $_POST['enddate'];

            $this->set('startDate', $startDate);
            $this->set('endDate', $endDate);
            $this->set('salesRange', $this->Client->repSalesOrdersRange($startDate, $endDate));
        }

    }
}
