<?php

namespace Sparclex\NovaImportCard;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Imtigger\LaravelJobStatus\JobStatus;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Laravel\Nova\Rules\Relatable;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Sparclex\NovaImportCard\Helpers\PendingDispatch;

class ImportController
{
    /**
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function handle(NovaRequest $request)
    {
        $resource = $request->newResource();
        $importerClass = $resource::$importer ?? Config::get('nova-import-card.importer');

        $data = Validator::make($request->all(), ['file' => 'required|file'])
            ->validate();

        /** @var \Sparclex\NovaImportCard\Contracts\Importer $importer */
        $importer = new $importerClass(get_class($resource));

        try {
            $dispatch = $importer->import($data['file']);

            if ($dispatch instanceof PendingDispatch) {
                return ['message' => __('Import queued'), 'queued' => true, 'status_id' => $dispatch->getJobStatusId()];
            }
        } catch (ImportException $e) {
            $this->responseError($e->getMessage());
        } catch (NoTypeDetectedException $e) {
            $this->responseError(__('Invalid file type'));
        }

        $message = method_exists($importer, 'message') ? $importer->message() : __('Import successful');

        return Action::message($message);
    }

    /**
     * @param string $resource
     * @param \Imtigger\LaravelJobStatus\JobStatus $status
     * @return \Imtigger\LaravelJobStatus\JobStatus
     */
    public function progress(string $resource, JobStatus $status)
    {
        return $status;
    }

    /**
     * @param $request
     * @param $resource
     * @return \Illuminate\Support\Collection
     */
    protected function extractValidationRules(NovaRequest $request, Resource $resource)
    {
        return collect($resource::rulesForCreation($request))
            ->mapWithKeys(function ($rule, $key) {
                foreach ($rule as $i => $r) {
                    if (!is_object($r)) {
                        continue;
                    }

                    // Make sure relation checks start out with a clean query
                    if (is_a($r, Relatable::class)) {
                        $rule[$i] = function () use ($r) {
                            $r->query = $r->query->newQuery();

                            return $r;
                        };
                    }
                }

                return [$key => $rule];
            });
    }

    private function responseError($error)
    {
        throw ValidationException::withMessages([
            0 => [$error],
        ]);
    }
}
