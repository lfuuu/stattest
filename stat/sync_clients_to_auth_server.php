<?php
define('NO_WEB',1);
define('PATH_TO_ROOT','./');
$_SERVER['SERVER_NAME'] = "stat.mcn.ru";
include PATH_TO_ROOT . "conf_yii.php";

set_time_limit(0);

$m = new MongoClient("mongodb://".MONGO_HOST, array("username" => MONGO_USER, "password" => MONGO_PASS, "db" => MONGO_DB));
$m_auth_service = $m->selectCollection(MONGO_DB, 'auth_product');
$m_auth_user = $m->selectCollection(MONGO_DB, 'auth_user');

$clients = $db->AllRecords("select z.rnd, z.tid, c.client, c.currency, c.status, c.balance,
                                    case c1.id is null when true then c.id else c1.id end login1,
                                    case c1.client is null  when true then c.client else c1.client end login2,
                                    case c1.password is null when true then c.password else c1.password end password
                            from z_sync_postgres z
                                left join clients c on c.id=z.tid
                                left join clients c1 on c1.client = SUBSTRING(c.client, 1, POSITION('/' in c.client)-1)
                            where tbase='auth' and tname='clients'");
foreach($clients as $client)
{
  $id = $client['tid'];
  while(strlen($id) < 22) $id = '0'.$id;
  $id = new MongoId('01'.$id);

  if ($client['client'] === null)
  {
    $m_auth_service->remove(array('_id'=>$id));
  }else{
    $r = $m_auth_service->findOne(array('_id'=>$id));
    if ($r === null)
    {
      $r = array(
        "_id" => $id,
        "_type" => array(
            "Core\\AuthProduct"
          ),
      );
    }
    $r["id"] = (int)$client['tid'];
    $r["status"] = $client['status'];
    $r["client"] = $client['client'];
    $r["currency"] = $client['currency'];
    $r["type"] = "lk";
    $r["server"] = "lk.mcn.ru";
    $m_auth_service->save($r);

    if ($client['password'] != '')
    {
      $user = $m_auth_user->findOne(array('login1' => $client['login1']));
      if ($user === null)
      {
        $user = array(
          "_type" => array(
            "Core\\AuthUser"
          ),
        );
      }
      $user['login1'] = $client['login1'];
      $user['login2'] = $client['login2'];
      $user['password'] = md5($client['password']);

      $m_auth_user->save($user);
      $m_auth_user->update(array('_id'=>$user['_id']), array('$addToSet'=>array('products'=>$id)));
    }

  }

  $db->QueryDelete('z_sync_postgres', array('tbase'=>'auth','tname'=>'clients','tid'=>$client['tid'],'rnd'=>$client['rnd']));

}

echo date('Y-m-d H:i:s')." OK\n";