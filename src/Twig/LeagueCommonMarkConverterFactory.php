<?php

namespace App\Twig;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\MarkdownConverter;

/**
 * @internal
 */
final class LeagueCommonMarkConverterFactory
{
    private $extensions;

    /**
     * @param ExtensionInterface[] $extensions
     */
    public function __construct(iterable $extensions)
    {
        $this->extensions = $extensions;
    }

    public function __invoke(): CommonMarkConverter
    {
        $config = [
            'external_link' => [
                'internal_hosts' => $_ENV['STATBUS_HOST'],
                'open_in_new_window' => true,
                'html_class' => 'external-link',
                'nofollow' => '',
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
            'default_attributes' => [
                Table::class => [
                    'class' => 'table table-bordered',
                ],
            ],
        ];

        $converter = new CommonMarkConverter($config);

        foreach ($this->extensions as $extension) {
            $converter->getEnvironment()->addExtension($extension);
        }
        $converter->getEnvironment()->addExtension(
            new DefaultAttributesExtension()
        );
        $converter->getEnvironment()->addExtension(
            new GithubFlavoredMarkdownExtension()
        );
        $converter->getEnvironment()->addExtension(
            new ExternalLinkExtension()
        );
        return $converter;
    }
}
