<?php

declare(strict_types=1);

namespace App\Rules\Wiki\Submission\Audio;

use App\Rules\Wiki\Submission\SubmissionRule;
use FFMpeg\FFProbe\DataMapping\Stream;
use Illuminate\Http\UploadedFile;

/**
 * Class AudioIndexStreamRule.
 */
class AudioIndexStreamRule extends SubmissionRule
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
        $streams = $this->streams()->all();

        return collect($streams)->contains(fn (Stream $stream) => $stream->isAudio() && $stream->get('index') === $this->expected);
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message(): string|array
    {
        return __('validation.submission.audio_index');
    }
}
