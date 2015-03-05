<?php
	define("PATH_TO_ROOT",'../stat/');
	include PATH_TO_ROOT . "conf_yii.php";

    $user->AuthorizeByUserId(Yii::$app->user->id);

	$action=get_param_raw('action');

	$table=get_param_raw('table'); if (!$table) return;
	$id=get_param_integer('id'); if (!$id) return;
	$hl=get_param_raw('hl'); 

    switch($table) {
        case 'usage_ip_ports': 
            if (!access('services_internet','edit') && !access('services_collocation','edit')) return;
            break;
        case 'usage_ip_routes':
            if (!access('services_internet','edit') && !access('services_collocation','edit')) return;
            break;
        case 'usage_voip':
            if (!access('services_voip','edit')) return;
            break;
        case 'domains':
            if (!access('services_domains','edit')) return;
            break;
        case 'usage_ip_ppp':
            if (!access('services_ppp','edit')) return;
            break;
        case 'bill_monthlyadd':
        case 'usage_extra':
            if (!access('services_additional','edit')) return;
            break;
        case 'emails':
            if (!access('services_mail','edit')) return;
            break;
        case 'usage_welltime': 
            if (!access('services_welltime','full')) return;
            break;
        case 'usage_virtpbx': 
            if (!access('services_welltime','full')) return;
            break;
        case 'usage_sms':
            if (!access('services_welltime','full')) return;
            break;
        default: return;
    }


	$design->assign('hl',$hl);	
	$dbf = DbFormFactory::Create($table);
	if (!$dbf) return;
	if (!$dbf->Load($id)) return;
	$ret = $dbf->Process();
	if (false && $ret=='edit') {
		header('Location: ?table='.$table.'&id='.$id);
		exit;
	}

use app\assets\AppAsset;
use \yii\helpers\Html;

$view = Yii::$app->view;

AppAsset::register($view);
?>
<?php $view->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
  <meta charset="<?= Yii::$app->charset ?>"/>
  <?= Html::csrfMetaTags() ?>
  <title><?= Html::encode($view->title) ?></title>
  <?php $view->head() ?>
</head>
<body style="padding: 15px;">
<?php $view->beginBody() ?>

<?php
	$dbf->nodesign=1;
	HelpDbForm::assign_log_history($table,$id);
	$dbf->Display(array('table'=>$table,'id'=>$id),$table,'Редактирование'.' id='.$id);

	echo $view->render('@app/views/layouts/widgets/messages');
	$design->display('dbform.tpl');
?>

<?php $view->endBody() ?>

<script>
  LOADED = 1;
  $(document).ready(function(){
    $('.select2').select2();
  });
</script>

</body>
</html>
<?php $view->endPage() ?>
