<?php
class StoreLimitReport
{
    public static function getData()
    {
        global $design,$user;
        $selected_user = get_param_integer('user', $user->Get('id'));
        $design->assign('user', $selected_user);
        
        if (!User::exists($selected_user))
        {
            trigger_error('Выбранный пользователь не существует');
            return;
        }
        
        $storeId = get_param_raw("store_id", "8e5c7b22-8385-11df-9af5-001517456eb1");
        $design->assign("store_id", $storeId);
        
        $options = array();
        $options['conditions'] = array('is_show = ?', 'yes');
        $options['order'] = 'name';
        $store_list = GoodStore::all($options);
        $design->assign("store_list", $store_list);

        $options = array();
        $options['select'] = 'u.usergroup, user, name, u.email, g.comment as ugroup, u.id';
        $options['from'] = 'user_users as u';
        $options['joins'] = 'LEFT JOIN user_groups as g ON u.usergroup = g.usergroup';
        $options['conditions'] = array('u.enabled = ?', 'yes');
        $options['order'] = 'u.usergroup';
        $users = User::all($options);
        $design->assign('users',$users);

        $data = GoodNotificationLimits::getLimitsByUser($selected_user);
        $design->assign('data', $data);

        $design->AddMain('tt/store_limit.tpl');
    }
    public static function saveData()
    {
        global $design;
        $user_id = get_param_integer('user_id','');
        
        if (!User::exists($user_id))
        {
            trigger_error('Выбранный пользователь не существует');
            $design->ProcessEx('errors.tpl');
            exit();
        }
        
        $options = array();
        $options['conditions'] = array('user_id = ?', $user_id);
        GoodNotificationLimits::delete_all($options);
        
        $products = get_param_raw('products', array());
        if (!empty($products))
        {
            foreach ($products as $good_id=>$v)
            {
                foreach ($v as $store_id => $limit_value)
                {
                    $data = new GoodNotificationLimits;
                    $data->user_id = $user_id;
                    $data->good_id = $good_id;
                    $data->store_id = $store_id;
                    $data->limit_value = $limit_value;
                    $data->save();
                }
            }
        }

        header('Location: ./?module=tt&action=store_limit&user='.$user_id);
        exit();
    }
}
?>
