<?php

/*
 * Plugin Name: JoolenPointsForMemberRegistration4
 *
 * Copyright(c) joolen inc. All Rights Reserved.
 *
 * https://www.joolen.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\JoolenPointsForMemberRegistration4\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\JoolenPointsForMemberRegistration4\Entity\PointAddHistoryForMemberRegistration;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * PointAddHistoryForMemberRegistrationRepository
 */
class PointAddHistoryForMemberRegistrationRepository extends AbstractRepository
{
    /**
     * Constructor.
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PointAddHistoryForMemberRegistration::class);
    }
}