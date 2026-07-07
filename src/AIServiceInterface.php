<?php

interface AIServiceInterface {
    /**
     * Takes raw text (e.g., chat logs, meeting notes) and extracts tasks.
     * 
     * @param string $text The raw brain dump text.
     * @return array An array of tasks. Each task must be an associative array with:
     *               - 'title' (string)
     *               - 'description' (string)
     *               - 'priority' (string: 'High', 'Medium', or 'Low')
     */
    public function extractTasks(string $text): array;
}
