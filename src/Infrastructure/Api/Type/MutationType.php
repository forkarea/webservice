<?php

namespace Krauza\Infrastructure\Api\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Krauza\Infrastructure\Api\Action\AddAnswer;
use Krauza\Infrastructure\Api\Action\CreateBox;
use Krauza\Infrastructure\Api\Action\CreateCard;
use Krauza\Infrastructure\Api\TypeRegistry;
use Krauza\Infrastructure\DataAccess\BoxRepository;
use Krauza\Infrastructure\DataAccess\BoxSectionsRepository;
use Krauza\Infrastructure\DataAccess\CardRepository;
use Krauza\Core\UseCase\CreateBox as CreateBoxUseCase;
use Krauza\Core\UseCase\CreateCard as CreateCardUseCase;
use Krauza\Core\UseCase\AddAnswer as AddAnswerUseCase;

class MutationType extends ObjectType
{
    public function __construct()
    {
        $config = [
            'name' => 'Mutation',
            'fields' => [
                'createBox' => [
                    'type' => TypeRegistry::getCreateBoxType(),
                    'args' => [
                        'name' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'Name of the box'
                        ]
                    ],
                    'resolve' => function ($rootValue, $args, $context) {
                        $boxRepository = new BoxRepository($context['database_connection']);
                        $boxUseCase = new CreateBoxUseCase($boxRepository, $context['id_policy']);
                        $createBox = new CreateBox($boxUseCase, $context['current_user']);
                        return $createBox->action($args);
                    }
                ],
                'createCard' => [
                    'type' => TypeRegistry::getCreateCardType(),
                    'args' => [
                        'box_id' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'The id of the parent box'
                        ],
                        'obverse' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'The obverse of the card'
                        ],
                        'reverse' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'The reverse of the card'
                        ]
                    ],
                    'resolve' => function ($rootValue, $args, $context) {
                        $boxRepository = new BoxRepository($context['database_connection']);
                        $boxSectionsRepository = new BoxSectionsRepository($context['database_connection']);
                        $cardRepository = new CardRepository($context['database_connection']);
                        $cardUseCase = new CreateCardUseCase($cardRepository, $boxSectionsRepository, $context['id_policy']);
                        $createCard = new CreateCard($cardUseCase, $boxRepository->getById($args['box_id']));
                        return $createCard->action($args);
                    }
                ],
                'addAnswer' => [
                    'type' => TypeRegistry::getAddAnswerType(),
                    'args' => [
                        'answer' => [
                            'type' => Type::nonNull(Type::boolean()),
                            'description' => 'The answer for the card. User knew the answer or not.'
                        ],
                        'box_id' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'The id of the box'
                        ],
                        'card_id' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'The id of the card'
                        ]
                    ],
                    'resolve' => function($rootValue, $args, $context) {
                        $boxRepository = new BoxRepository($context['database_connection']);
                        $cardRepository = new CardRepository($context['database_connection']);
                        $boxSectionsRepository = new BoxSectionsRepository($context['database_connection']);
                        $addAnswerUseCase = new AddAnswerUseCase($boxSectionsRepository);
                        $addAnswer = new AddAnswer(
                            $addAnswerUseCase,
                            $cardRepository->get($args['card_id']),
                            $boxRepository->getById($args['box_id'])
                        );
                        return $addAnswer->action($args);
                    }
                ]
            ]
        ];

        parent::__construct($config);
    }
}
