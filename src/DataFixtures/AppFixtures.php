<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Invoice;
use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('en_US');

        for ($u = 0; $u < 10; $u++) {
            $chrono = 1;

            $user = new User();
            $user
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setPassword($this->encoder->hashPassword($user, 'password'));

            $manager->persist($user);

            for ($c = 0; $c < mt_rand(10, 20); $c++) {
                $customer = new Customer();

                $customer
                    ->setFirstName($faker->firstName)
                    ->setLastName($faker->lastName)
                    ->setEmail($faker->email)
                    ->setCompany($faker->company)
                    ->setUser($user);

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
                        ->setChrono($chrono++);

                    $manager->persist($invoice);
                }
            }
        }

        $manager->flush();
    }
}
