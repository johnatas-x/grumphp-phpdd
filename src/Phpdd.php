<?php

declare(strict_types=1);

namespace GrumphpPhpdd;

use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Phpdd task.
 */
final class Phpdd extends AbstractExternalTask
{

  public static function getConfigurableOptions(): ConfigOptionsResolver {
    $resolver = new OptionsResolver();

    $resolver->setDefaults([
      'files' => [],
      'target' => '',
      'after' => '',
      'exclude' => [],
      'max_size' => '',
      'file_extensions' => [
        'php',
        'php5',
        'phtml'
      ],
      'skip_checks' => [],
    ]);

    $resolver->addAllowedTypes('files', ['array']);
    $resolver->addAllowedTypes('target', ['string', 'null']);
    $resolver->addAllowedTypes('after', ['string', 'null']);
    $resolver->addAllowedTypes('exclude', ['array']);
    $resolver->addAllowedTypes('max_size', ['string', 'null']);
    $resolver->addAllowedTypes('file_extensions', ['array']);
    $resolver->addAllowedTypes('skip_checks', ['array']);

    return ConfigOptionsResolver::fromOptionsResolver($resolver);
  }

  public function canRunInContext(ContextInterface $context): bool
  {
    return $context instanceof GitPreCommitContext || $context instanceof RunContext;
  }

  public function run(ContextInterface $context): TaskResultInterface
  {
    $config = $this->getConfig()->getOptions();

    $files_path = $config['files'];
    $extensions = $config['file_extensions'];

    $files = $context->getFiles();
    if (\count($files_path)) {
      $files = $files->paths($files_path);
    }
    $files = $files->extensions($extensions);

    if (0 === \count($files)) {
      return TaskResult::createSkipped($this, $context);
    }

    $arguments = $this->processBuilder->createArgumentsForCommand('phpdd');
    $arguments->addFiles($files);
    $arguments->add('--no-interaction');
    $arguments->addOptionalCommaSeparatedArgument('--file-extensions=%s', $extensions);
    $arguments->addOptionalCommaSeparatedArgument('--exclude=%s', $config['exclude']);

    $string_opt_args = ['target', 'after', 'max_size'];
    $array_opt_args = ['exclude', 'skip_checks'];

    foreach ($string_opt_args as $string_opt_arg) {
      if (!empty($config[$string_opt_arg])) {
        $arguments->addOptionalArgument("--$string_opt_arg=%s", $config[$string_opt_arg]);
      }
    }

    foreach ($array_opt_args as $array_opt_arg) {
      if (!empty($config[$array_opt_arg])) {
        $arg_name = str_replace('_', '-', $array_opt_arg);
        $arguments->addOptionalCommaSeparatedArgument("--$arg_name=%s", $config[$array_opt_arg]);
      }
    }

    $process = $this->processBuilder->buildProcess($arguments);
    $process->run();

    if (!$process->isSuccessful()) {
      return TaskResult::createFailed($this, $context, $this->formatter->format($process));
    }

    return TaskResult::createPassed($this, $context);
  }

}