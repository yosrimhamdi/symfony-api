<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Invoice;
use App\Entity\Customer;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');

        for ($c = 0; $c < mt_rand(6, 10); $c++) {
            $customer = new Customer();

            $customer
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setCompany($faker->company);

            $manager->persist($customer);

            for ($i = 0; $i < mt_rand(2, 5); $i++) {
                $invoice = new Invoice();

                $invoice
                    ->setCustomer($customer)
                    ->setAmount($faker->randomFloat(2, 500, 1000))
                    ->setSentAt(
                        new \DateTimeImmutable(
                            $faker
                                ->dateTimeBetween('-6 months')
                                ->format('Y-m-d H:i:s')
                        )
                    )
                    ->setStatus(
                        $faker->randomElement(['PAID', 'SENT', 'CANCELLED'])
                    )
                    ->setChrono($i + 1);

                $manager->persist($invoice);
            }
        }

        $manager->flush();
    }
}
