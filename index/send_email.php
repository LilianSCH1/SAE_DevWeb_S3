echo 'Message envoyé avec succès!';
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi du message: {$mail->ErrorInfo}";
    }
} else {
    echo 'Méthode non autorisée.';
}
=======
        $mail->send();
        header('Location: contact.php?status=success');
        exit();
    } catch (Exception $e) {
        header('Location: contact.php?status=error&message=' . urlencode($mail->ErrorInfo));
        exit();
    }
} else {
    header('Location: contact.php?status=invalid');
    exit();
}
