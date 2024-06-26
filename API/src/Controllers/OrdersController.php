<?php

namespace DavidsBookClub\Controllers;

use DavidsBookClub\enums\storedProcedures;
use DavidsBookClub\Utils\Database;
use DavidsBookClub\Utils\MessageManager;
use DavidsBookClub\Utils\SecurityManager;

class OrdersController
{
    /**
     * This function returns orders, for the admin panel.
     * It gets called with lazy loading, so it only returns some items at a time.
     * @param array $payload This parameter holds how many times have been returned so far, to the API caller. The data is within `$payload['totalReceivedItems']`.
     *               To determine how many items to return with, theres the `$payload['limit']`. Useful for different screen sizes, when returning data.
     */
    public function getOrders($payload)
    {
        if (isset($payload['totalReceivedItems'], $payload['limit'])) {
            // Call SP, to make sure that the user (userId provided by the JWT) is an admin.
            $jwt = SecurityManager::decodeJwt($_COOKIE['jwt']);
            $userRole = Database::callStoredProcedure(storedProcedures::GetUserRole, ["userId" => $jwt->sub]); // DOES NOT EXIST

            if ($userRole != "Admin") {
                MessageManager::sendError("Insufficient permissions", 401);
            }

            // Call SP, which gets the amount of total received items and how many to return
            $orders = Database::callStoredProcedure(storedProcedures::GetOrderDetails, [""]);

            if (true) {
                // If any rows are returned then they get returned to the API caller here
                $arrayOfRows = "";
                MessageManager::sendSuccess($arrayOfRows);
            } else {
                MessageManager::sendError("No orders left to return", 404);
            }
        } else {
            MessageManager::missingParameters();
        }
    }


    /**
     * This function checks if the coupon from the checkout is valid, for the webshop.
     * @param array $payload This parameter is the coupon input from the API caller. It can be retrieved with $payload['coupon'].
     */
    public function verifyCoupon($payload)
    {
        if (isset($payload['coupon'])) {
            $securityManager = new SecurityManager;

            // Call SP to get all encrypted coupons

            foreach ($CouponsInDatabase as $coupon) {
                // Decrypt each coupon until a match is found
                $securityManager->decryptData($coupon);

                // If match is found for coupon
                if (true) {
                    MessageManager::sendSuccess("Valid coupon code");
                    exit;
                }
            }

            MessageManager::sendError("No matching coupon", 404);
        } else {
            MessageManager::missingParameters();
        }
    }

    /**
     * This function searches for order info and returns specific orders which match that info.
     * Displaying orders uses lazy loading, so therefore it.
     * @param array $payload `$payload['searchInput']` has the searched text, which is used to return specific orders to the API caller.
     *               `$payload['totalReceivedItems']` is used as a start index, for which rows should be returned, to include lazy loading.
     *               `$payload['limit']` is used for the API caller to determine how many items should be returned.
     */
    public function searchOrders($payload)
    {
        if (isset($payload['searchInput'], $payload['totalReceivedItems'], $payload['limit'])) {

            // Call SP to get the user role.
            $jwt = SecurityManager::decodeJwt($_COOKIE['jwt']);
            $userRole = Database::callStoredProcedure(storedProcedures::GetUserRole, ["userId" => $jwt->sub]); // DOES NOT EXIST

            if ($userRole != "Admin") {
                MessageManager::sendError("Insufficient permissions", 401);
            }


            // Call SP to find orders matching based on a payload
            $storedProcedureParameters = array("totalReceivedItems" => $payload['totalReceivedItems'], "orderLimit" => $payload['limit'], "userRole" => "admin", "filterValue" => $payload['searchInput']);
            $orders = Database::callStoredProcedure(storedProcedures::GetFilteredOrderDetails, $storedProcedureParameters);

            MessageManager::sendSuccess($orders);
        } else {
            MessageManager::missingParameters();
        }
    }

    /**
     * Uses the user JWT to identify the user, into a userId. It then displays a lazy loaded list, of the orders made by the user.
     * @param array $payload This parameter holds how many times have been returned so far, to the API caller. The data is within `$payload['totalReceivedItems']`.
     *               To determine how many items to return with, theres the `$payload['limit']`. Useful for different screen sizes, when returning data.
     */
    public function getUserOrders($payload)
    {
        if (isset($payload['totalReceivedItems'], $payload['limit'])) {
            // Call SP to return the users order.
            $jwt = SecurityManager::decodeJwt($_COOKIE['jwt']);

            $orders = Database::callStoredProcedure(storedProcedures::GetUserOrdersDetails, ["totalReceivedItems" => $payload['totalReceivedItems'], "limit" => $payload['limit'], "userId" => $jwt->sub]);
            MessageManager::sendSuccess($orders);
        } else {
            MessageManager::missingParameters();
        }
    }

    /**
     * This function maps a zip code to a city name.
     * @param array $payload `$payload['zipCode']` is used as an input for which city should be returned.
     */
    public function getCityFromZipCode($payload)
    {
        if (isset($payload['zipCode'])) {
            $cityName = Database::callStoredProcedure(storedProcedures::GetCityByZipCode, ["zipCode" => $payload['zipCode']]);
            MessageManager::sendSuccess($cityName[0]);
        } else {
            MessageManager::missingParameters();
        }
    }

    /**
     * This function creates an order.
     * @param array $payload This parameter holds the input required for creating an order. The information required are:
     *               `$payload['email']`
     *               `$payload['name']`
     *               `$payload['streetName']`
     *               `$payload['zipCode']`
     *               `$payload['houseNumber']`
     *               `$payload['phoneNumber']`
     *               `$payload['city']`
     *               `$payload['coupon']`
     *               `$payload['products']['productId']` The product id is a numeric value, not just a string saying "productId".
     *               `$payload['products']['productId']['quantity']`
     *               `$payload['products']['productId']['price']`
     *               `$payload['totalPrice']`
     */
    public function createOrder($payload)
    {
        if (isset(
            $payload['email'],
            $payload['name'],
            $payload['streetName'],
            $payload['zipCode'],
            $payload['houseNumber'],
            $payload['phoneNumber'],
            $payload['city'],
            $payload['products'],
            $payload['totalPrice']
        )) {
            foreach ($payload['products'] as $products) {
                // Verify that the products have valid productIds, price and quantities.
            }

            // Call SP to check if user exists

            if (false) {
                // If user does not exist, then create the user and return user id, using SP.
            }

            // Create order using SP. Return order_id.

            MessageManager::sendSuccess("Order created");
        } else {
            MessageManager::missingParameters();
        }
    }
}
