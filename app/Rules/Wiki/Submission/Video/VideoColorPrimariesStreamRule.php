<?php

declare(strict_types=1);

namespace App\Rules\Wiki\Submission\Video;

use App\Rules\Wiki\Submission\SubmissionRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

/**
 * Class VideoColorPrimariesStreamRule.
 */
class VideoColorPrimariesStreamRule extends SubmissionRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  UploadedFile  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $video = Arr::first(
            $this->streams(),
            fn (array $stream) => Arr::get($stream, 'codec_type') === 'video'
        );

        return in_array(
            Arr::get($video, 'color_primaries'),
            ['bt709', 'smpte170m', 'bt470bg']
        );
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message(): string|array
    {
        return __('validation.submission.video_color_primaries');
    }
}
