package com.zad.automation.constants;

public final class FrameworkConstants {

    private FrameworkConstants() {}

    public static final String BASE_URL =
            System.getenv().getOrDefault(
                    "BASE_URL",
                    "http://localhost/grad_project/web/"
            );

    public static final String ADMIN_LOGIN_URL =
            BASE_URL + "admin/login.php";

    public static final String ADMIN_REQUESTS_URL =
            BASE_URL + "admin/requests.php";

    public static final String ADMIN_PRODUCTS_URL =
            BASE_URL + "admin/products.php";

    public static final int EXPLICIT_WAIT = 25;
}
