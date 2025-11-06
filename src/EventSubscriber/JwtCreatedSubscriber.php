<?php

namespace App\EventSubscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JwtCreatedSubscriber implements EventSubscriberInterface
{
    /**
     * @param JWTCreatedEvent $event
     */
    public function onJwtCreated(JWTCreatedEvent $event): void
    {
        // Récupérer l'utilisateur à partir de l'événement
        $user = $event->getUser();

        // Récupérer le payload (les données du jeton)
        $payload = $event->getData();

        // Ajouter les rôles de l'utilisateur au payload
        $payload['roles'] = $user->getRoles();

        // Mettre à jour le payload de l'événement
        $event->setData($payload);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'lexik_jwt_authentication.on_jwt_created' => 'onJwtCreated',
        ];
    }
}
