<?php

const BEFORE_SAVE = 'vtiger.entity.beforesave';
const FIRST_ID = '001';

class EventHandler extends VTEventHandler
{

    private $moduleDateFieldMapping = array(
        "Invoice" => Array(
            "prefix" => "RNG",
            "date_field" => "invoicedate"
        ),
        "Quotes" => Array(
            "prefix" => "ANG",
            "date_field" => null
        )
    );

    function handleEvent($event, $entity)
    {
        $module = $entity->getModuleName();

        if (!$entity->isNew() || !$this->isApplicableFor($module)) {
            return;
        }

        switch ($event) {
            case BEFORE_SAVE:
                $this->beforeSave($entity);
                break;

            default:
                # NOOP
                break;
        }
    }

    function beforeSave($entity)
    {
        $module = $entity->getModuleName();

        if ($this->dateFieldFor($module) != null) {
            $date = $entity->get($this->dateFieldFor($module)); 
        } else {
            $date = "";
        }

        if (empty($date)) {
            $date = $this->now();
        } else {
            $date = DateTimeField::convertToDBFormat($date);
            $date = DateTimeField::convertToUserTimeZone($date);
        }

        $yearAndMonthPrefix = $date->format('Ym');
        $monthSpecificPrefix = $this->prefixFor($module) . $yearAndMonthPrefix . '-';
        $nextId = $this->nextIdFor($module, $monthSpecificPrefix);

        if ($nextId == null) {
            $nextId = FIRST_ID;
        }

        $this->focusFor($entity)
            ->setModuleSeqNumber("configure", $module, $monthSpecificPrefix, $nextId);
    }

    function nextIdFor($module, $monthSpecificPrefix)
    {
        $query = $this->db()->pquery("
            SELECT cur_id
            FROM vtiger_modentity_num
            WHERE semodule=? AND prefix like ?",
            array($module, $monthSpecificPrefix));

        return $this->db()->query_result($query, 0, 'cur_id');
    }

    function now()
    {
        global $current_user, $default_timezone;
        $timeZone = $current_user->time_zone ? $current_user->time_zone : $default_timezone;
        $date = new DateTime(date("Y-m-d H:i:s"));
        $date->setTimezone(new DateTimeZone($timeZone));
        return $date;
    }

    public function focusFor($entity)
    {
        if (!$entity->focus) {
            $entity->focus = CRMEntity::getInstance($entity->getName());
        }

        return $entity->focus;
    }

    function isApplicableFor($module)
    {
        return array_key_exists($module, $this->moduleDateFieldMapping);
    }

    function dateFieldFor($module)
    {
        return $this->moduleDateFieldMapping[$module]['date_field'];
    }

    function prefixFor($module)
    {
        return $this->moduleDateFieldMapping[$module]['prefix'];
    }

    function db()
    {
        return PearDatabase::getInstance();
    }

}
