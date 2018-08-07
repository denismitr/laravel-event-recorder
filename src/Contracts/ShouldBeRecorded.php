<?php

namespace Denismitr\EventRecorder\Contracts;

interface ShouldBeRecorded
{
    /**
     * Get important properties of the event
     * that should be persisted
     *
     * @return array
     */
    public function getProperties(): array;

    /**
     * This can be left unimplemented
     *
     * Basically this allows to prepare a custom human readable description for an event
     *
     * @return string
     */
    public function getDescription(): ?string;
}