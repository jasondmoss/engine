<?php

namespace HelloFresh\Engine\EventStore\Snapshot;

use HelloFresh\Engine\Domain\AggregateIdInterface;
use HelloFresh\Engine\Domain\AggregateRootInterface;
use HelloFresh\Engine\Domain\DomainMessage;

class Snapshotter
{
    /**
     * @var SnapshotStoreInterface
     */
    private $snapshotStore;

    /**
     * @var SnapshotStrategyInterface
     */
    protected $strategy;

    /**
     * Snapshotter constructor.
     * @param SnapshotStoreInterface $snapshotStore
     * @param SnapshotStrategyInterface $strategy
     */
    public function __construct(SnapshotStoreInterface $snapshotStore, SnapshotStrategyInterface $strategy)
    {
        $this->snapshotStore = $snapshotStore;
        $this->strategy = $strategy;
    }

    public function take(AggregateRootInterface $aggregate, DomainMessage $message)
    {
        $id = $aggregate->getAggregateRootId();

        if (!$this->strategy->isFulfilled($aggregate)) {
            return false;
        }

        if (!$this->snapshotStore->has($id, $message->getVersion())) {
            $this->snapshotStore->save(Snapshot::take($id, $aggregate, $message->getVersion()));
        }
    }

    /**
     * @param AggregateIdInterface $id
     * @return Snapshot
     */
    public function get(AggregateIdInterface $id)
    {
        return $this->snapshotStore->byId($id);
    }
}
