<?php

declare(strict_types=1);

namespace App\Rules\Wiki\Submission\Format;

use App\Rules\Wiki\Submission\SubmissionRule;
use Illuminate\Http\UploadedFile;

/**
 * Class TotalStreamsFormatRule.
 */
class TotalStreamsFormatRule extends SubmissionRule
{
    /**
     * Create new rule instance.
     *
     * @param  int  $expected
     */
    public function __construct(protected readonly int $expected)
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  UploadedFile  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $streams = $this->streams();

        return $streams->count() === $this->expected;
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message(): string|array
    {
        return __('validation.submission.format_total_streams');
    }
}
