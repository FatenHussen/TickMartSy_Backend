<?php

return [

    'Success'           => 'Operation completed successfully.',
    'Error'             => 'An error occurred, please try again later.',
    'NotFound'          => 'The requested item was not found.',
    'Unauthorized'      => 'You are not authorized to perform this action.',
    'Forbidden'         => 'Access denied.',
    'ValidationError'   => 'The given data was invalid.',
    'Created'           => 'Item created successfully.',
    'Updated'           => 'Data updated successfully.',
    'Deleted'           => 'Item deleted successfully.',
    'invalid_verification_code' => 'Invalid verification code',
    'invalid_coupon' => 'invalid coupon',
    'inactive_account' => 'Account is inactive',
    // HTTP Status Codes
    'errors' => [
        400 => 'Bad request.',
        401 => 'Unauthorized.',
        403 => 'Forbidden.',
        404 => 'The requested item was not found.',
        405 => 'Method not allowed.',
        409 => 'Conflict detected.',
        422 => 'Unprocessable entity.',
        429 => 'Too many requests, please try again later.',
        500 => 'Server error, please try again later.',
        503 => 'Service unavailable.',
    ],
    'custom_error' => 'Something went wrong. Please try again later.',

    'wrong_credential' => 'Invalid login credentials.',

    'subscription_blocked_existing' => 'Vous avez deja un abonnement en attente ou actif.',

    'wrong_password' => 'The current password is incorrect.',

    'otp_invalid' => 'The verification code is invalid or expired.',

    'otp_valid' => 'The verification code is invalid or has expired.',

    'password_valid' => 'The reset code is invalid or has expired.',

    'service_orders' => [
        'status_updated_successfully' => 'Statut de la commande de service mis à jour avec succès.',
        'cannot_change_final_status' => 'Impossible de modifier le statut d\'une commande de service clôturée.',
        'service_not_available' => 'Le service demandé n\'est pas disponible dans la boutique sélectionnée.',
    ],

    'custom_order_requests' => [
        'created_successfully' => 'Votre commande rapide a été envoyée et est en attente de tarification.',
        'approved_successfully' => 'Commande approuvée. La préparation va commencer.',
        'cancelled_successfully' => 'Commande personnalisée annulée.',
        'cancelled_by_admin_successfully' => 'Commande personnalisée annulée par l\'administration.',
        'converted_successfully' => 'Commande convertie en commande système, en attente d\'approbation client.',
        'not_found' => 'Demande de commande personnalisée introuvable.',
        'address_not_found' => 'Adresse de livraison introuvable ou non associée à vous.',
        'payment_method_unavailable' => 'Mode de paiement indisponible.',
        'cannot_approve' => 'Impossible d\'approuver cette demande dans son état actuel.',
        'cannot_cancel' => 'Impossible d\'annuler cette demande dans son état actuel.',
        'order_not_ready' => 'La commande système liée n\'est pas prête pour approbation.',
        'already_priced' => 'Cette demande a déjà été tarifée.',
        'variance_required_for_external' => 'Le type et la valeur de variation sont requis pour les articles externes.',
    ],
];
