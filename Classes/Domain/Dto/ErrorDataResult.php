<?php
declare(strict_types=1);

namespace Flowpack\Neos\Matomo\Domain\Dto;

/*
 * This script belongs to the Neos CMS package "Flowpack.Neos.Matomo".
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

class ErrorDataResult extends AbstractDataResult
{
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            'message' => join(',', $this->results)
        ];
    }
}
