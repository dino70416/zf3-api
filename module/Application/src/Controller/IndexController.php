<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Service\PdoDb;
use Application\Service\PdoTest;
use Application\TcApi\SemesterData;
use Application\TcApi\TcApi;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Model\ViewModel;

class IndexController extends BaseController
{
    public function indexAction()
    {

//        /** @var  $request \Zend\Http\PhpEnvironment\Request */
//        $request = $this->getServiceManager()->get('Request');
//        echo $request->getEnv('PHP_VERSION');

//        $config = $this->getServiceManager()->get('Config');
//        $db =$config['db'];
//
//        $dsn = 'mysql:host='.$db['host'].';dbname='.$db['dbname'].';charset='.$db['charset'];
//
//        $dbh = new \PDO( $dsn, $db['user'] , $db['password']);


        /** @var  $dbh \PDO */
        $dbh = $this->getServiceManager()->get('pdodb');

        $sql = "SELECT * FROM student";
        $arr = $dbh->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $viewModel = new ViewModel();

        $viewModel->setVariable('data', $arr);

        return $viewModel;

    }

    public function testAction()
    {

        // 取得 PDO 物件
        /** @var  $pdo \PDO */
        $pdo = $this->getServiceManager()->get(PdoDb::class);

        // 取出班級資料
        $sql = "SELECT * FROM semester_class";
        $res = $pdo->query($sql)->fetchAll();

        $arr = [];
        foreach ($res as $row) {
            $key = $row['id'];
            $arr[$key] = $row['grade'].'年'. $row['class_name'].'班 ('.$row['tutor'].')';
        }


        //  建立班級表單
        $form = new Form('semester_form');

        $form->add([
            'name' => 'class_id',
            'type' => Select::class,
            'options' => [
                'label' => '班級',
                'value_options' => $arr
            ]
        ]);

        // 取出 post 傳值
        $data = $this->params()->fromPost();

        // 填入表單預設值
        $form->setData($data);
        $viewModel = new ViewModel();

        // 如果選擇班級
        if (isset($data['class_id'])) {
            $sql = "SELECT a.* , b.number FROM student a
            JOIN semester_student b ON a.id=b.student_id
            JOIN semester_class c ON b.semester_class_id=c.id 
            WHERE c.id=".$data['class_id'];

            $sql .= " ORDER BY b.number";

            $res = $pdo->query($sql)->fetchAll();
            $viewModel->setVariable('data', $res);

        }

        $viewModel->setVariable('select_form', $form);
        return $viewModel;
    }

    public function helpAction()
    {

        $arr = [
            'abc' =>'123456',
            'def' => '567880'
        ];

        $viewModel = new ViewModel();

        $viewModel->setVariable('data', $arr);

        return $viewModel;

    }

    public function test2Action()
    {
        echo 'this a test!!';
        exit;
    }


    public function barcodeAction()
    {

        $type = $this->params()->fromRoute('type');
        echo "type :".$type;
        $label  = $this->params()->fromRoute('label');
        echo "label: ". $label;
        $abc = $this->params()->fromQuery('abc');
        echo "abc: ". $abc;

        print_r($this->params()->fromQuery());
    }


    public function showUsersAction()
    {
        //$verbose = $this->request->getParam('verbose') ;
        $classId = $this->params()->fromRoute('class_id');

        $sql = "select * from semester_class where grade = $classId";
        echo $sql;
        $pdo = $this->sm->get(PdoTest::class);
        $arr = $pdo->query($sql)->fetchAll();

        print_r($arr);

    }
}
