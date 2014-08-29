<?php

/*
 * This file is part of the broadway/broadway package.
 *
 * (c) Qandidate.com <opensource@qandidate.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Broadway\Saga;

use Broadway\Domain\DomainMessageInterface;
use Broadway\EventDispatcher\EventDispatcherInterface;
use Broadway\Events;
use Broadway\Saga\Metadata\MetadataFactoryInterface;
use Broadway\Saga\State\RepositoryInterface;
use Broadway\Saga\State\StateManager;

/**
 * SagaManager that manages multiple sagas.
 */
class MultipleSagaManager implements SagaManagerInterface
{
    private $repository;
    private $stateManager;
    private $eventDispatcher;

    public function __construct(
        RepositoryInterface $repository,
        array $sagas,
        StateManager $stateManager,
        MetadataFactoryInterface $metadataFactory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->repository      = $repository;
        $this->sagas           = $sagas;
        $this->stateManager    = $stateManager;
        $this->metadataFactory = $metadataFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handles the event by delegating it to Saga('s) related to the event.
     */
    public function handle(DomainMessageInterface $domainMessage)
    {
        $event = $domainMessage->getPayload();

        foreach ($this->sagas as $sagaType => $saga) {
            $metadata = $this->metadataFactory->create($saga);

            if (! $metadata->handles($event)) {
                continue;
            }

            $state = $this->stateManager->findOneBy($metadata->criteria($event), $sagaType);

            if (null === $state) {
                continue;
            }
            $this->eventDispatcher->dispatch(
                SagaManagerInterface::EVENT_PRE_HANDLE,
                array($sagaType, $state->getId())
            );

            $newState = $saga->handle($event, $state);

            $this->eventDispatcher->dispatch(
                SagaManagerInterface::EVENT_POST_HANDLE,
                array($sagaType, $state->getId())
            );

            $this->repository->save($newState, $sagaType);
        }
    }
}
