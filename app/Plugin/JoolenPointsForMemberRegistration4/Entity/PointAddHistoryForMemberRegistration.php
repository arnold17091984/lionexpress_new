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

namespace Plugin\JoolenPointsForMemberRegistration4\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Entity\Customer;

if (!class_exists('\Plugin\JoolenPointsForMemberRegistration4\Entity\PointAddHistoryForMemberRegistration')) {
    /**
     * PointAddHistoryForMemberRegistration
     *
     * @ORM\Table(name="plg_joolen_dtb_point_add_history_for_member_registration")
     * @ORM\InheritanceType("SINGLE_TABLE")
     * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
     * @ORM\HasLifecycleCallbacks()
     * @ORM\Entity(repositoryClass="Plugin\JoolenPointsForMemberRegistration4\Repository\PointAddHistoryForMemberRegistrationRepository")
     */
    class PointAddHistoryForMemberRegistration
    {
        /**
         * ポイント付与タイプ:常時付与
         */
        const TYPE_NORMAL = 1;

        /**
         * ポイント付与タイプ:期間中のみ付与
         */
        const TYPE_PERIOD_LIMITED = 2;

        /**
         * @ORM\Column(name="id", type="integer", options={"unsigned":true})
         * @ORM\Id
         * @ORM\GeneratedValue(strategy="IDENTITY")
         */
        private $id;

        /**
         * ポイントを付与した会員
         *
         * @var Customer
         *
         * @ORM\ManyToOne(targetEntity="Eccube\Entity\Customer", inversedBy="CustomerAddresses")
         * @ORM\JoinColumns({
         *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
         * })
         */
        private $Customer;

        /**
         * ポイント付与時の会員のemail
         *
         * @var string
         *
         * @ORM\Column(name="email", type="string", length=255)
         */
        private $email;

        /**
         * ポイント付与タイプ
         *
         * @var string
         *
         * @ORM\Column(name="type", type="integer")
         */
        private $type;

        /**
         * @return int
         */
        public function getId()
        {
            return $this->id;
        }

        /**
         * @param Customer|null $customer
         * @return PointAddHistoryForMemberRegistration
         */
        public function setCustomer(Customer $customer = null)
        {
            $this->Customer = $customer;

            return $this;
        }

        /**
         * @return Customer|null
         */
        public function getCustomer()
        {
            return $this->Customer;
        }

        /**
         * @param string $email
         * @return PointAddHistoryForMemberRegistration
         */
        public function setEmail($email)
        {
            $this->email = $email;

            return $this;
        }

        /**
         * @return string
         */
        public function getEmail()
        {
            return $this->email;
        }

        /**
         * @param int $type
         * @return PointAddHistoryForMemberRegistration
         */
        public function setType($type)
        {
            $this->type = $type;

            return $this;
        }

        /**
         * @return int
         */
        public function getType()
        {
            return $this->type;
        }
    }
}