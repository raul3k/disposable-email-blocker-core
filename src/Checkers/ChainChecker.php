<?php

declare(strict_types=1);

namespace Raul3k\BlockDisposable\Core\Checkers;

use InvalidArgumentException;

class ChainChecker implements CheckerInterface
{
    /** @var CheckerInterface[] */
    private array $checkers;

    private ?string $lastMatchedChecker = null;

    /**
     * @param CheckerInterface[] $checkers
     */
    public function __construct(array $checkers)
    {
        if (empty($checkers)) {
            throw new InvalidArgumentException('At least one checker is required');
        }

        foreach ($checkers as $checker) {
            if (!$checker instanceof CheckerInterface) {
                throw new InvalidArgumentException(
                    sprintf(
                        'All checkers must implement %s',
                        CheckerInterface::class
                    )
                );
            }
        }

        $this->checkers = array_values($checkers);
    }

    public function isDomainDisposable(string $normalizedDomain): bool
    {
        $this->lastMatchedChecker = null;

        foreach ($this->checkers as $checker) {
            if ($checker->isDomainDisposable($normalizedDomain)) {
                $this->lastMatchedChecker = $checker::class;

                return true;
            }
        }

        return false;
    }

    /**
     * Get the class name of the last checker that matched.
     * Returns null if no match or isDomainDisposable hasn't been called.
     */
    public function getLastMatchedChecker(): ?string
    {
        return $this->lastMatchedChecker;
    }

    /**
     * Add a checker to the chain.
     */
    public function addChecker(CheckerInterface $checker): self
    {
        $this->checkers[] = $checker;

        return $this;
    }

    /**
     * Get all checkers in the chain.
     *
     * @return CheckerInterface[]
     */
    public function getCheckers(): array
    {
        return $this->checkers;
    }
}
