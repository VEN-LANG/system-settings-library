<?php

/**
 * Job class responsible for updating a system setting.
 * This job handles updating a key-value pair in the system settings
 * through the SystemSettingsService.
 */

namespace Venom\SystemSettings\Jobs;

use /**
 * Trait Queueable
 *
 * This trait provides methods to set and retrieve properties related to queue operations.
 * It is commonly used in jobs, events, or other components that are dispatched to a queue for deferred handling.
 * The trait simplifies implementation of queuing logic by providing helper methods.
 *
 * Features include:
 * - Setting target connection for the queue.
 * - Specifying the queue name.
 * - Specifying a delay before the job is processed.
 * - Chaining configurations for fluent usage.
 *
 * Methods:
 * - onConnection: Specify the connection to be used for the queued job.
 * - onQueue: Set the target queue name for the job.
 * - delay: Set a delay for processing the job.
 * - through: Specify middleware to wrap the queued job/process.
 *
 * To use this trait, include it in a class that needs to interact with a queue system and utilize the provided methods.
 */
    Illuminate\Bus\Queueable;
use /**
 * This interface is a contract for defining a class that should be placed onto a queue.
 *
 * Classes implementing this interface indicate that they are "queueable,"
 * meaning they can be processed by a queue worker later.
 *
 * It is primarily used in Laravel's job handling system to
 * specify that a job should be queued instead of immediately executed.
 *
 * Typically, a class that implements ShouldQueue should also extend
 * Laravel's base job class for additional functionality and proper processing.
 */
    Illuminate\Contracts\Queue\ShouldQueue;
use /**
 * This trait provides methods to support the dispatching of jobs in Laravel applications.
 * It includes a static `dispatch` method that allows instantiation and dispatch of a job
 * in a single call by utilizing the Laravel service container and the queue system.
 *
 * Typically used in Laravel Jobs to streamline the process of dispatching jobs onto
 * the application's queue.
 */
    Illuminate\Foundation\Bus\Dispatchable;
use /**
 * Trait InteractsWithQueue
 *
 * Provides methods to interact with the queue system within Laravel's queuing infrastructure.
 * This trait can be used by classes that need to perform actions such as deleting,
 * releasing, or managing the processing of queued jobs.
 */
    Illuminate\Queue\InteractsWithQueue;
use /**
 * This trait is used to enable automatic serialization and deserialization
 * of Eloquent models and their relationships when they are passed to queued
 * jobs. The primary purpose of this trait is to ensure that models are
 * properly rehydrated when a job runs, maintaining their original state.
 *
 * It serializes models into an identifier (usually the primary key) when
 * they are queued, and rehydrates them into full model instances when the
 * job is executed.
 *
 * This trait aids in maintaining data consistency, especially when dealing
 * with queued tasks relying on Eloquent models.
 */
    Illuminate\Queue\SerializesModels;
use /**
 * SystemSettingsService is responsible for managing application-wide system settings.
 *
 * This service provides methods to retrieve, update, and delete settings stored within
 * the application. System settings are typically used for configuring and managing the
 * behavior of the system at a global level.
 *
 * Key features:
 * - Fetch system settings by key
 * - Update settings values
 * - Delete settings when no longer required
 *
 * It acts as an interface to handle system settings, abstracting away the implementation
 * details of how settings are stored and retrieved.
 */
    Venom\SystemSettings\Services\SystemSettingsService;

/**
 * Handles updating system settings by processing the provided key and value.
 */
class UpdateSettingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Represents a key used for accessing or identifying a specific value in a collection, array, or data structure.
     */
    protected $key;
    /**
     * Represents a variable whose purpose is to hold a value.
     * The type and usage of the value depend on the associated context.
     */
    protected $value;

    /**
     * Constructor for initializing the object with key and value.
     *
     * @param mixed $key The key to initialize.
     * @param mixed $value The value to associate with the key.
     * @return void
     */
    public function __construct($key, $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * Handles the process of updating a system setting with the specified key and value.
     *
     * @param SystemSettingsService $settingsService The service responsible for managing system settings.
     * @return void
     */
    public function handle(SystemSettingsService $settingsService)
    {
        $settingsService->set($this->key, $this->value);
    }
}