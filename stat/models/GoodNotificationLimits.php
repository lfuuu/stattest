<?php
class GoodNotificationLimits extends ActiveRecord\Model
{
    static $table_name = 'g_notification_limits';
    /**
     * Возвращает информацию о продуктах, оповещения о которых будут приходить пользователю
     * @param int $user_id ID пользователя
     */
    function getLimitsByUser($user_id)
    {
        $options = array();
        $options['select'] = 'gl.*, g.name, g.art, g.num_id, gs.qty_free,  s.name as store_name ';
        $options['from'] = 'g_notification_limits AS gl';
        $options['joins'] = 'LEFT JOIN 
                                g_good_store as gs ON (gl.good_id = gs.good_id AND gl.store_id = gs.store_id) 
                             LEFT JOIN 
                                g_goods as g ON (g.id = gl.good_id) 
                             LEFT JOIN 
                                g_store as s ON (s.id = gl.store_id) ';
        $options['conditions'] = array('user_id = ?', $user_id);
        $options['order'] = 'gl.good_id ';
        $data = GoodNotificationLimits::all($options);
        return $data;
    }
    /**
     * Возвращает информацию о продуктах, оповещения о которых будут приходить пользователям
     */
    function getAllLimits()
    {
        $options = array();
        $options['select'] = 'gl.*, g.name, g.art, g.num_id, gs.qty_free,  s.name as store_name, u.email ';
        $options['from'] = 'g_notification_limits AS gl';
        $options['joins'] = 'LEFT JOIN 
                                user_users as u ON (u.id = gl.user_id) 
                             LEFT JOIN 
                                g_good_store as gs ON (gl.good_id = gs.good_id AND gl.store_id = gs.store_id) 
                             LEFT JOIN 
                                g_goods as g ON (g.id = gl.good_id) 
                             LEFT JOIN 
                                g_store as s ON (s.id = gl.store_id)';
        $options['conditions'] = array('u.email <> ?', '');
        $options['order'] = 'gl.user_id, gl.good_id ';
        $data = GoodNotificationLimits::all($options);
        
        $users = array();
        if (!empty($data))
        {
            foreach ($data as $v)
            {
                if (!isset($users[$v->user_id]))
                {
                    $users[$v->user_id] = array();
                }
                $users[$v->user_id][] = $v;
            }
        }
        return $users;
    }
}