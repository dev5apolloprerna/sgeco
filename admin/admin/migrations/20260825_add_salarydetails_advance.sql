ALTER TABLE `salarydetails`
    ADD COLUMN `advance` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `deductionifany`;