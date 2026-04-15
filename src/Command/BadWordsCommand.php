<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:badwords', description: 'Gérer la liste des mots interdits')]
class BadWordsCommand extends Command
{
    private string $badWordsFile;

    public function __construct(string $badWordsFile)
    {
        parent::__construct();
        $this->badWordsFile = $badWordsFile;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'add, remove, list')
            ->addArgument('word', InputArgument::OPTIONAL, 'Mot à ajouter ou supprimer');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');

        if (!file_exists($this->badWordsFile)) {
            file_put_contents($this->badWordsFile, json_encode([]));
        }

        $words = json_decode(file_get_contents($this->badWordsFile), true);

        switch ($action) {
            case 'list':
                $io->table(['Mots interdits'], array_map(fn($w) => [$w], $words));
                break;

            case 'add':
                $word = strtolower(trim($input->getArgument('word')));
                if (!$word) {
                    $io->error('Veuillez fournir un mot.');
                    return Command::FAILURE;
                }
                if (!in_array($word, $words)) {
                    $words[] = $word;
                    file_put_contents($this->badWordsFile, json_encode($words, JSON_PRETTY_PRINT));
                    $io->success("Mot '$word' ajouté.");
                } else {
                    $io->warning("Le mot '$word' est déjà dans la liste.");
                }
                break;

            case 'remove':
                $word = strtolower(trim($input->getArgument('word')));
                if (!$word) {
                    $io->error('Veuillez fournir un mot.');
                    return Command::FAILURE;
                }
                $key = array_search($word, $words);
                if ($key !== false) {
                    unset($words[$key]);
                    file_put_contents($this->badWordsFile, json_encode(array_values($words), JSON_PRETTY_PRINT));
                    $io->success("Mot '$word' supprimé.");
                } else {
                    $io->warning("Le mot '$word' n'existe pas dans la liste.");
                }
                break;

            default:
                $io->error("Action inconnue. Utilisez 'list', 'add' ou 'remove'.");
                return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}