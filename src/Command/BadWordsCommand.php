<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:badwords', description: 'Manage forum bad words list')]
class BadWordsCommand extends Command
{
    public function __construct(private readonly string $badWordsFile)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'add, remove or list')
            ->addArgument('word', InputArgument::OPTIONAL, 'Word to add or remove');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = (string) $input->getArgument('action');

        if (!is_file($this->badWordsFile)) {
            file_put_contents($this->badWordsFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        $words = json_decode((string) file_get_contents($this->badWordsFile), true);
        $words = is_array($words) ? array_values(array_filter($words, 'is_string')) : [];

        switch ($action) {
            case 'list':
                $io->table(['Bad words'], array_map(static fn (string $word): array => [$word], $words));
                return Command::SUCCESS;

            case 'add':
                $word = mb_strtolower(trim((string) $input->getArgument('word')));
                if ($word === '') {
                    $io->error('Please provide a word to add.');
                    return Command::FAILURE;
                }

                if (!in_array($word, $words, true)) {
                    $words[] = $word;
                    sort($words);
                    file_put_contents($this->badWordsFile, json_encode($words, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    $io->success(sprintf('"%s" added.', $word));
                    return Command::SUCCESS;
                }

                $io->warning(sprintf('"%s" already exists.', $word));
                return Command::SUCCESS;

            case 'remove':
                $word = mb_strtolower(trim((string) $input->getArgument('word')));
                if ($word === '') {
                    $io->error('Please provide a word to remove.');
                    return Command::FAILURE;
                }

                $key = array_search($word, $words, true);
                if ($key === false) {
                    $io->warning(sprintf('"%s" was not found.', $word));
                    return Command::SUCCESS;
                }

                unset($words[$key]);
                file_put_contents($this->badWordsFile, json_encode(array_values($words), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $io->success(sprintf('"%s" removed.', $word));
                return Command::SUCCESS;
        }

        $io->error("Unknown action. Use 'list', 'add' or 'remove'.");

        return Command::FAILURE;
    }
}
