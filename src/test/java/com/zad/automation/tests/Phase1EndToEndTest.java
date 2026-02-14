package com.zad.automation.tests;

import com.zad.automation.base.BaseTest;
import com.zad.automation.flows.Phase1Flow;
import org.testng.annotations.Test;

public class Phase1EndToEndTest extends BaseTest {

    @Test
    public void fullPhase1Flow() {

        Phase1Flow flow = new Phase1Flow(driver);

        flow.registerRestaurant();
        flow.approveRestaurant();
        flow.loginAsRestaurant();
        flow.manageProducts();
    }
}
