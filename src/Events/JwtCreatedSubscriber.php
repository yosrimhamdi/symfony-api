<?php

namespace App\Events;

class JwtCreatedSubscriber
{
    public function updateJwtData($event)
    {
        $user = $event->getUser();
        $data = $event->getData();

        $data['firstName'] = $user->getFirstName();
        $data['lastName'] = $user->getLastName();

        $event->setData($data);
    }
}
