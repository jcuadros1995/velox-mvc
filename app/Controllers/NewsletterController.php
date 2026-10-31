
<?php

class NewsletterController extends Controller
{
    public function subscribe(): void
    {
        $email = '';

        try {
            $input = json_decode(
                file_get_contents('php://input'),
                true
            );

            if (!is_array($input)) {
                $input = $_POST;
            }

            $email = strtolower(trim(
                (string) ($input['email'] ?? '')
            ));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->json([
                    'success' => false,
                    'message' => 'Ingresa un correo electrónico válido.'
                ], 400);
                return;
            }

            if (strlen($email) > 254) {
                $this->json([
                    'success' => false,
                    'message' => 'El correo electrónico es demasiado largo.'
                ], 400);
                return;
            }

            $db = Database::getConnection();

            $stmt = $db->prepare(
                "SELECT id, status, welcome_sent_at
                 FROM tb_newsletter_subscribers
                 WHERE email = :email
                 LIMIT 1"
            );

            $stmt->execute(['email' => $email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing && $existing['status'] === 'active') {
                $this->json([
                    'success' => true,
                    'message' => 'Este correo ya está suscrito.'
                ]);
                return;
            }

            if ($existing) {
                $stmt = $db->prepare(
                    "UPDATE tb_newsletter_subscribers
                     SET status = 'active',
                         subscribed_at = NOW()
                     WHERE id = :id"
                );

                $stmt->execute([
                    'id' => $existing['id']
                ]);
            } else {
                $stmt = $db->prepare(
                    "INSERT INTO tb_newsletter_subscribers
                        (email, status)
                     VALUES
                        (:email, 'active')"
                );

                $stmt->execute(['email' => $email]);

                $existing = [
                    'id' => (int) $db->lastInsertId(),
                    'welcome_sent_at' => null
                ];
            }

            // Enviar bienvenida solamente si no consta como enviada.
            if (empty($existing['welcome_sent_at'])) {
                $emailService = new EmailService();

                $emailService->sendNewsletterWelcome($email);

                $stmt = $db->prepare(
                    "UPDATE tb_newsletter_subscribers
                     SET welcome_sent_at = NOW()
                     WHERE id = :id"
                );

                $stmt->execute([
                    'id' => $existing['id']
                ]);
            }

            $this->json([
                'success' => true,
                'message' => '¡Gracias por suscribirte! Revisa tu correo.'
            ]);

        } catch (Throwable $e) {
            error_log(
                '[NEWSLETTER_SUBSCRIBE] ' .
                $e->getMessage()
            );

            $this->json([
                'success' => false,
                'message' =>
                    'No pudimos completar la suscripción. Inténtalo nuevamente.'
            ], 500);
        }
    }
}
