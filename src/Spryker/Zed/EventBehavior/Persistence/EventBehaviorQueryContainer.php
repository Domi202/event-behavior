<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\EventBehavior\Persistence;

use DateTime;
use Orm\Zed\EventBehavior\Persistence\Base\SpyEventBehaviorEntityChangeQuery as BaseSpyEventBehaviorEntityChangeQuery;
use Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery;
use Propel\Runtime\Propel;
use Spryker\Shared\Config\Config;
use Spryker\Shared\Propel\PropelConstants;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;
use Throwable;

/**
 * @method \Spryker\Zed\EventBehavior\Persistence\EventBehaviorPersistenceFactory getFactory()
 */
class EventBehaviorQueryContainer extends AbstractQueryContainer implements EventBehaviorQueryContainerInterface
{
    public const TABLE_EXISTS = 'exists';

    /**
     * @api
     *
     * @param int $processId
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryEntityChange($processId)
    {
        $query = $this->getFactory()
            ->createEventBehaviorEntityChangeQuery()
            ->filterByProcessId($processId)
            ->orderByIdEventBehaviorEntityChange();

        return $query;
    }

    /**
     * @api
     *
     * @param \DateTime $date
     *
     * @return \Orm\Zed\EventBehavior\Persistence\SpyEventBehaviorEntityChangeQuery
     */
    public function queryLatestEntityChange(DateTime $date)
    {
        $query = $this->getFactory()
            ->createEventBehaviorEntityChangeQuery()
            ->filterByCreatedAt($date, Criteria::LESS_THAN)
            ->orderByIdEventBehaviorEntityChange();

        return $query;
    }

    /**
     * @api
     *
     * @return bool
     */
    public function eventBehaviorTableExists()
    {
        if (!class_exists(BaseSpyEventBehaviorEntityChangeQuery::class) ||
            !class_exists(SpyEventBehaviorEntityChangeQuery::class)
        ) {
            return false;
        }

        try {
            $con = Propel::getConnection();
            $params = [];
            $sql = "SELECT 1 FROM information_schema.tables WHERE table_name = 'spy_event_behavior_entity_change'";
            if (Config::get(PropelConstants::ZED_DB_ENGINE) === Config::get(PropelConstants::ZED_DB_ENGINE_MYSQL)) {
                $sql .= " AND `table_schema` = ?";
                $params[] = Config::get(PropelConstants::ZED_DB_DATABASE);
            }
            $sql .= ';';
            $stmt = $con->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            $stmt = null;
            $con = null;

            if (!$result) {
                return $result;
            }

            return true;
        } catch (Throwable $t) {
            /*
             *  Any error or exception shows the database
             *  is not ready for transactions.
             */
            return false;
        }
    }
}
