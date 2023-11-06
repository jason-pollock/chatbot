<?php

namespace CoolGuy\WordPress\Plugin\ChatBot;

class ChatApi
{
    public const REST_NAMESPACE = 'chatbot/v1';

    public function createRoute(string $endpoint, string $method, callable $callback, callable $permission_callback): void
    {
        add_action('rest_api_init', function () use ($endpoint, $method, $callback, $permission_callback) {
            register_rest_route(self::REST_NAMESPACE, $endpoint, [
                'methods' => $method,
                'callback' => $callback,
                'permission_callback' => $permission_callback,
            ]);
        });
    }

    private function initializeChat(\WP_REST_Request $request): \WP_REST_Response
    {
        // Initialize chat with article content
        $articleContent = $request->get_param('articleContent');
        // Process the article content to fit within the token limits
        $context = $this->summarizeArticleContent($articleContent);
        // Store the context in the session or database associated with the user/session ID
        $this->storeChatContext($context);

        return new \WP_REST_Response('Chat initialized with context.', 200);
    }

    private function handleMessage(\WP_REST_Request $request): \WP_REST_Response
    {
        $input = $request->get_param('message');
        $context = $this->retrieveChatContext(); // Retrieve the context you stored earlier
        $chatExchange = [$context, $input];

        return $this->handleBotResponse($chatExchange);
    }

    private function summarizeArticleContent(string $articleContent): string
    {
        // This is a placeholder until we can implement actual summarization logic.
        // For now just truncate the content to a max length to avoid exceeding token limits.
        $maxLength = 1000;

        return substr($articleContent, 0, $maxLength);
    }

    private function handleBotResponse(array $chatExchange): \WP_REST_Response
    {
        $prompt = implode("\n\n", $chatExchange);

        try {
            $responseData = $this->callOpenaiApi($prompt);
        } catch (\JsonException) {
            // TODO: Error handling.
            return new \WP_REST_Response('Invalid JSON payload.', 500);
        }

        if (!isset($responseData['choices'][0]['text'])) {
            // TODO: Error handling.
            return new \WP_REST_Response('Error processing the message.', 500);
        }

        $responseText = $responseData['choices'][0]['text'];

        $this->updateChatContext($chatExchange['context'], $chatExchange['userMessage'], $responseText);

        return new \WP_REST_Response($responseText, 200);
    }

    /**
     * @throws \JsonException
     */
    private function callOpenaiApi(string $prompt): array
    {
        $apiKey = $_ENV['OPENAI_API_KEY'];
        $apiUrl = 'https://api.openai.com/v1/completions'; // Base URL for OpenAI API
        $headers = [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ];
        $body = [
            'model' => 'text-davinci-003',
            'prompt' => $prompt,
            'max_tokens' => 150,
//            'prompt' => $prompt,
//            'max_tokens' => 150,
//            'temperature' => 0.9,
//            'top_p' => 1,
//            'frequency_penalty' => 0.0,
//            'presence_penalty' => 0.6,
//            'stop' => ['\n\n'],
        ];
        $response = wp_remote_post($apiUrl, [
            'method' => 'POST',
            'headers' => $headers,
            'body' => json_encode($body),
        ]);
        if (is_wp_error($response)) {
            throw new \RuntimeException($response->get_error_message());
        }
        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            $responseMessage = wp_remote_retrieve_response_message($response);
            throw new \RuntimeException("OpenAI API error: {$responseMessage} ({$responseCode})");
        }

        return json_decode(wp_remote_retrieve_body($response), true, 512, JSON_THROW_ON_ERROR);
    }

    private function updateChatContext(string $context, string $userMessage, string $botResponse): void
    {
        // Append the new exchange to the existing context
        $updatedContext = $context . "\nUser: " . $userMessage . "\nBot: " . $botResponse;
        // Store the updated context back into the session
        $this->storeChatContext($updatedContext);
    }

    private function retrieveChatContext(): string
    {
        // Retrieve the context you stored earlier
        if (!session_id()) {
            session_start();
        }

        return $_SESSION['chat_context'] ?? '';
    }

    private function storeChatContext(string $context): void
    {
        // For simplicity, we'll store this in the session
        // Make sure to start the session in your main plugin file or init hook
        if (!session_id()) {
            session_start();
        }
        $_SESSION['chat_context'] = $context;
    }

}
