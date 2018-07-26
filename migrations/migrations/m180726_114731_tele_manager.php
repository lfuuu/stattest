<?php

/**
 * Class m180726_114731_tele_manager
 */
class m180726_114731_tele_manager extends \app\classes\Migration
{
    /**
     * Up
     */
    public function safeUp()
    {
        throw new Exception('manual start');

        $this->_setManagerBnv();
        $this->_setAccountManagerPma();
    }

    private function _setAccountManagerPma()
    {

        $query = \app\models\ClientContract::find()
            ->where([
                'account_manager' => ['polutornova', 'bagumanov', 'sinichev', 'timocshenko', 'usachev', 'lebedeva', 'simonyan']
            ]);

        /** @var \app\models\ClientContract $contract */
        foreach ($query->each() as $contract) {

            if (!$this->_getIsActive($contract)) {
                continue;
            }

            echo ". ";

            $contract->account_manager = 'pma';

            if (!$contract->save()) {
                throw new \app\exceptions\ModelValidationException($contract);
            }
        }
    }

    private function _setManagerBnv()
    {

        $query = \app\models\ClientContract::find()
            ->where([
                'business_id' => \app\models\Business::TELEKOM,
                'manager' => 'pma'
            ]);

        /** @var \app\models\ClientContract $contract */
        foreach ($query->each() as $contract) {

            if (!$this->_getIsActive($contract)) {
                continue;
            }

            echo ". ";

            $contract->manager = 'bnv';

            if (!$contract->save()) {
                throw new \app\exceptions\ModelValidationException($contract);
            }
        }

    }

    public function _getIsActive(\app\models\ClientContract $contract)
    {
        foreach ($contract->accounts as $account) {
            if ($account->is_active) {
                return true;
            }
        }

        return false;
    }

    /**
     * Down
     */
    public function safeDown()
    {
        // nothing
    }
}
