<?php

class f000001_users extends app\classes\Migration
{
    public function up()
    {
        $this->applyFixture('user_groups');
        $this->applyFixture('user_users');

        $authManager = new \app\classes\AuthManager();
        $authManager->updateDatabase();

        $this->execute("
            insert into user_grant_groups(name,resource,access)
                select 'admin', resource, `values` from user_rights;
        ");
    }
}
