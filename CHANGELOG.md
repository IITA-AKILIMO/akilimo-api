CHANGELOG
====

`WIP` 05/04/2023 14:04
----
 * refactor: revised dependencies.
 * refactor: revised user endpoints.
 * Merge pull request #201 from IITA-AKILIMO/fix/payload-null-content.
 * fix: null checks for payload response.

`7.1.0` 11/01/2023 10:06
----
 * Merge pull request #200 from IITA-AKILIMO/masgeek-patch-1.
 * Update Dockerfile.
 * Merge pull request #198 from IITA-AKILIMO/feat/legacy-recommendation.
 * feat: added legacy endpoint for recommendations.

`7.0.1` 09/01/2023 13:46
----
 * Merge pull request #196 from IITA-AKILIMO/feat/legacy-endpoint.
 * Merge branch 'develop' into feat/legacy-endpoint.
 * fix: added legacy api version endpoint.

`7.0.0` 09/01/2023 13:36
----
 * Merge pull request #195 from IITA-AKILIMO/masgeek-patch-1.
 * Update config.yml.
 * feat: updated docker build file.
 * Merge pull request #194 from IITA-AKILIMO/fix/docker-build.
 * ci: updated build files.
 * Merge pull request #193 from IITA-AKILIMO/6.1.1-branch.
 * ci: updte docker version.
 * ci: updated build image to 17.0.0-node.
 * feat: updated gradle dependencies.
 * build: updated ocker jre version.
 * Merge branch '6.1.1-branch' of github.com:IITA-AKILIMO/akilimo-api into 6.1.1-branch.
 * feat: updated dependancies version.
 * Merge branch 'develop' into 6.1.1-branch.
 * fix: updated mariadb dependencies.
 * fix: updated dependencies.
 * Merge pull request #192 from IITA-AKILIMO/fix/dialect.
 * Updated dialects.
 * Merge pull request #191 from IITA-AKILIMO/build/mariadb.
 * Merge branch 'develop' into build/mariadb.
 * build: updated dependencies.
 * Merge pull request #190 from IITA-AKILIMO/feat/country-migration.
 * feat: updated currency model and dto.
 * feat: added migration.
 * docs: updated README.md.
 * Merge pull request #186 from IITA-AKILIMO/feature/split-api.
 * refactor: updated enum files.
 * build: adde dmigrations and enum values.
 * fix: added burundian currency.
 * refactor: merged changes fom develop.
 * Merge remote-tracking branch 'origin/develop' into feature/split-api.
 * Merge pull request #187 from IITA-AKILIMO/opena-api.
 * Merge branch 'feature/split-api' of github.com:IITA-AKILIMO/akilimo-api into feature/split-api.
 * docs: updated Open api specs.
 * Added open api specs.
 * docs: updated efault open api website.
 * docs: updated API and README documents.
 * build: added thinder client test requests.
 * feat: added basic fr endpoint.
 * feat: updated request for basic api.
 * feat: revised ferterlizer request payload.
 * feat: revised pro api for FR.
 * docs: updated the README file.

`6.1.1` 05/04/2022 13:59
----
 * Merge pull request #184 from IITA-AKILIMO/feat/investment_amount_burundi.
 * fix: missing investment amount.

`6.1.0` 05/04/2022 12:20
----
 * Merge pull request #182 from IITA-AKILIMO/feature/burundi.
 * feat: burundi cassava prices.
 * feat: burindi fertilizer prices.
 * feat: added burundi.
 * refactor: typo correction.
 * docs: updated README document.
 * docs: updated docs folder.
 * feat: added basic use cases endpoint.
 * style: updated logback log patterns.
 * feat: updated rate limit.
 * build: updated dfeault property.
 * feat(added-rate-limiting): added rate limiting to handle DDOS mitigation.
 * docs: updated naming on pro rest controller.
 * feat: added bpp for pro api.
 * feat: added BPP endpoint.
 * feat: added IC processing.
 * feat: added processing for FR.
 * feat: added use case specific endpoints.
 * feat: added default properties values.
 * refactor: updated all endpoints to user version 1.
 * ci: updated docker image names.
 * Update README.md.
 * refactor: updated branch reference.

`6.0.1` 16/12/2021 09:54
----
 * Merge pull request #179 from IITA-AKILIMO/dox/api-metadata.
 * Merge branch 'develop' into dox/api-metadata.
 * docs: updated openapi dynamic variables.
 * Main (#178).

`6.0.0` 24/11/2021 10:08
----
 * Merge pull request #164 from IITA-AKILIMO/feature/rwanda-data.
 * fix: invalid opening braces.
 * Merge branch 'develop' into feature/rwanda-data.
 * fix: added ghana fertilizers evaluation.
 * Merge branch 'develop' into feature/rwanda-data.
 * feat: added migrations.
 * feat: added fertilizer prices evaluation.
 * feat: added investment amount migration.

`5.0.0` 22/11/2021 21:51
----
 * Merge pull request #173 from IITA-AKILIMO/fix/new-data.
 * feat: added test data for new fertilizers.
 * feat: migrations.
 * feat: added ref key.
 * feat: added unique fertilizer country key.
 * feat: added fertelizer migration by country.
 * ci: renamed docker file image.
 * Merge pull request #171 from IITA-AKILIMO/feature/gahan-data.
 * feat: ghana data processing.
 * feat: added ghana data.

`4.6.0` 22/10/2021 13:44
----
 * Merge pull request #169 from IITA-AKILIMO/feature/recod-size.
 * feat: record size header.

`4.5.1` 08/10/2021 12:41
----
 * Merge pull request #167 from IITA-AKILIMO/fix/file-seperator.
 * fix: added platform independent path seperator.

`4.5.0` 08/10/2021 11:57
----
 * Merge pull request #165 from IITA-AKILIMO/feature/csv-download.
 * feat: csv downloading.
 * feat: ona data resource.
 * feat: rwanda data.
 * feat: added rwanda metadata.
 * docs: updated links.
 * Merge pull request #162 from masgeek/doc/nextrelease.
 * docs: updated changelog file.

`4.0.1` 13/05/2021 10:01
----
 * Merge pull request #161 from masgeek/fix/sms.
 * fix: corrected invalid sms message generation.
 * Merge pull request #159 from masgeek/fix/messaging.
 * fix: fixed properties mapping name path.
 * docs: updated changelog file.

`4.0.0` 11/05/2021 16:00
----
 * Merge pull request #158 from masgeek/fix/null-tzs-rate.
 * fix: fixed null value due to incorrect key property reference.
 * Merge pull request #157 from masgeek/fix/security-exception.
 * fix: added user feedback to security exception.
 * Merge pull request #156 from masgeek/fix/triggers.
 * fix: corrected migration script names to match migration file xml names.
 * fix: made functions migrations rerunnable.
 * refactor: added runalways tag to view migration.
 * fix: corrected trigger calls naming.
 * Merge pull request #155 from masgeek/fix/missing-migration.
 * fix: added missing migration for stats view updates.
 * Merge pull request #154 from masgeek/fix/view-migration.
 * fix: fixed view migration.
 * Merge pull request #153 from masgeek/fix/dependencies.
 * fix: fixed jdbs database driver.
 * docs: disabled PR templated momentarily.
 * perf: updated library dependencies.
 * Merge pull request #152 from masgeek/feature/feedback-endpoint.
 * ci: change docker image name.
 * fix: added app request stats filter for easy filtering.
 * fix: added excluded flag to stats view script.
 * feat: added migrations for sp and triggers.
 * feat: added app request stats endpoint.
 * fix: added scripts to updated null and empty columns in app report table.
 * feat: added endpoint for fetching request statistics.
 * feat: added excluded paths to web security.
 * refactor: added exception throwing when record is not found.
 * feat: added migration to add unique constrains to authority table.
 * feat: Added endpoints for adding user.
 * feat: added migration for auth database.
 * feat: added endpoint security.
 * fix: added better query sorting.
 * fix: removed swagger annotations.
 * Merge pull request #151 from masgeek/doc/pr-template.
 * docs: update PR template.
 * Merge pull request #150 from masgeek/feature/new-migrations.
 * feat: add migrations.
 * Merge pull request #148 from masgeek/fix/docker-build.
 * fix: corrected docker build sequence and steps.
 * Merge pull request #147 from masgeek/chore/refactor.
 * refactor: removed timestamper.sh file.
 * ci: updated circleci build workflow to generate api and migration images.
 * refactor: add new git hook.
 * fix: fixed invalid package names that prevented bean generation.
 * fix: updated application config files.
 * fix: modularized migrations to independently running module.
 * fix: fixed invalid reference.
 * refactor: modularization or project.
 * Merge pull request #145 from masgeek/feature/app-use-case-report.
 * ci: changed auto aproval actor to check for repo owner.
 * refactor: fixed table encoding formart in migration.
 * docs: Updated github PR template and CODEOWNERS file.
 * refactor: revised sql view script path name.
 * feat: migrations for app request stats.
 * ci: removed uneeded python code formaytter @black.
 * ci: added dicumentation in precomit config file.
 * ci: added new hook and rearranged the order.
 * docs: Updated changelog file.
 * refactor: added new Enum values.

`3.9.0` 28/04/2021 13:37
----
 * Merge pull request #144 from masgeek/feature/user-feedback-rev.
 * feat: added migration for user type column.
 * Merge pull request #142 from masgeek/feat/changelog.
 * ci: added new todo generator.
 * docs: Updated pull request template style.
 * ci: changed target build branch.
 * ci: added todo github actions.
 * docs(Readme-updates): readme updates.
 * style(added-git-hooks-and-formatted-styles): style formatting.

`3.8.3` 19/04/2021 12:27
----
 * Merge pull request #140 from masgeek/fix/user-feedback.
 * REvised user feedback request object.

`3.8.2` 03/03/2021 08:54
----
 * Merge pull request #138 from masgeek/fix/missing_fertilizer.
 * Added NPK201216 Fertilizer type.

`3.8.1` 02/03/2021 16:42
----
 * Merge pull request #136 from masgeek/fix/price-and-migration.
 * Added untracked migration * added checker for min and max prices.

`3.8.0` 02/03/2021 12:05
----
 * Merge pull request #134 from masgeek/feature/user-feedback.
 * Moved device token to body.
 * Moved device token to body.
 * Added user feedback ogic.
 * REvised column naming.
 * Removed unsued lombok library and references.

`3.7.1` 14/02/2021 17:53
----
 * Merge pull request #132 from masgeek/fix/gender-property.
 * Added Gender data property.

`3.7.0` 11/02/2021 14:37
----
 * Merge pull request #131 from masgeek/fix/main-activity.
 * Updated build timestamp.
 * Adde migration for potato and cassava prices.
 * Added migrations to modify maize min and max prices.
 * Removed hardcoded 'Aout' in fertilizer price.
 * Merge pull request #129 from masgeek/fix/wrong-import-order.
 * Updated master changelog runner.

`3.6.3` 02/02/2021 20:41
----
 * Merge pull request #128 from masgeek/fix/missed-import-file.
 * Added missed sml tracking file.
 * Merge pull request #126 from masgeek/fix/prices.
 * Updates to maize rices.
 * Updates to cross conversion of minimum price based on unit of sale.
 * Added currency resource.
 * Updated gradle things.
 * Updated app root name.

`3.6.2` 19/01/2021 12:31
----
 * Merge pull request #124 from masgeek/fix/prices.
 * Updated currency conversion routine and logic.
 * Added currency lookup logic.
 * Added currency migrations.
 * Added currencies models.
 * Updates.
 * Merge pull request #123 from masgeek/fix/currency-payload.
 * REmoved gradle.properties file.
 * Merge remote-tracking branch 'origin/fix/currency-payload' into fix/currency-payload.
 * Updated JDK version.
 * Merge branch 'develop' into fix/currency-payload.
 * Added new logic for currency computation.
 * Added new logic for currency computation.
 * Addded new migrations.
 * Updated fertilizers.
 * Merge pull request #122 from masgeek/fix/default-fertilizer.
 * Added default fertilizer assignment for when the list is empty.
 * Merge pull request #121 from masgeek/feature/pagination.
 * Added pagination for better loading.
 * Added pagination capability.
 * Merge pull request #118 from masgeek/fix/translated-units.
 * Added temporary check for translated are units.

`3.6.1` 25/08/2020 12:50
----
 * Merge pull request #116 from masgeek/fix/empty-fertilizer-list.
 * Updates.
 * Updated rate limiter logging.
 * Added empty fertilizer checker.
 * Removed sql files as it had sensistive info.
 * Updated gradle version.
 * Merge branch 'develop' of github.com:masgeek/akilimo-api into develop.

`3.6.0` 26/06/2020 17:36
----
 * Merge pull request #114 from masgeek/fix/revert-yaramila.
 * Disabled yaramil unik fertilizer.

`3.5.1` 24/06/2020 15:11
----
 * Merge pull request #113 from masgeek/fix/revert-yaramila.
 * Reverted back yaramila fertilizer.
 * Merge pull request #111 from masgeek/fix/remove-yaramila.
 * Merge remote-tracking branch 'origin/develop' into develop.
 * Disabled yaramil unik fertilizer.

`3.5.0` 22/06/2020 09:45
----
 * Merge pull request #110 from masgeek/fix/null-plumber-response.
 * Added better null check for plumber responses.
 * Added better null check for plumber responses.
 * Merge pull request #109 from masgeek/fix/sms-errors.
 * Added more details in error logging.
 * Increases the scope of the try catch.
 * Merge pull request #107 from masgeek/feature/added-country-price.
 * Corrected country code, value being passed was dialing code.
 * Updated device identifier.
 * Updated logic for processing phone number and sms sending.
 * Added branded country code skipper.
 * Added branded country code skipper.
 * Added default sender flag for local numbers.
 * Merge remote-tracking branch 'origin/develop' into develop.

`3.4.0` 18/06/2020 00:12
----
 * Merge pull request #105 from masgeek/feature/added-country-price.
 * Added country price.
 * Merge branch 'develop' of github.com:masgeek/akilimo-api into develop.

`3.3.2` 04/06/2020 12:23
----
 * Merge pull request #103 from masgeek/fix/sms-content-validation.
 * Revised error catching and reporting.
 * Added better checks for sms content.

`3.3.1` 03/06/2020 11:27
----
 * Merge pull request #101 from masgeek/fix/language.
 * Added better checks for email sending.

`3.3.0` 24/05/2020 00:39
----
 * Merge pull request #99 from masgeek/feature/sweet-potato-prices.
 * Updated default USD price to be 0 instead of -1.
 * Added potato prices repo and resources.
 * added sweet portto default pice data.
 * Added sweet potato prices import.

`3.2.0` 20/05/2020 18:26
----
 * Merge pull request #98 from masgeek/feature/new-fertilizer.
 * Chnaged fertilizer sort order.
 * Added try catch section.
 * Merge pull request #97 from masgeek/fix/wring-fertilizer_name.
 * Corrected wrong fertilizer name.
 * Merge pull request #95 from masgeek/feature/new-fertilizer.
 * Updated the code to take in new fertilizer migrations.
 * Added new fertilizer import.

`3.1.1` 16/05/2020 18:43
----
 * Merge pull request #93 from masgeek/fix/handle-http-errors.
 * Added try catch section.

`3.1.0` 15/05/2020 18:18
----
 * Merge pull request #91 from masgeek/feature/sms-sending.
 * Changed default plumber service url.
 * Updated docker entrypoints.
 * Updated configurations for sms sending.
 * Updated sms services and properties.

`3.0.0` 11/05/2020 20:16
----
 * Merge pull request #90 from masgeek/feature/dynamic-variables.
 * Updated jpa properties.
 * Updated docker entry point file.
 * Merge pull request #89 from masgeek/fix/rate-limit.
 * Added rate limit default to be a minute.
 * Merge pull request #87 from masgeek/feature/demo-endpoint.
 * Added log message.
 * Added dev context switching for urls.
 * Updated resources to support dev context.
 * Added rate limit interceptor.

`2.0.1` 11/05/2020 18:59
----
 * Merge pull request #85 from masgeek/fix/lazy-classes.
 * Made variables of inherited classes open and not final.

`2.0.0` 24/04/2020 15:06
----
 * Merge pull request #83 from masgeek/feature/add-maiz-prices-column.
 * Reduced code duplication for maize and cassava prices.
 * Refactored functions.
 * Refactored variable names.
 * Renamed cassava price classes to be prodcue price.
 * Added sort column to maize price table.
 * Added maize prices migration.

`1.2.0` 24/04/2020 10:14
----
 * Merge pull request #81 from masgeek/fix/add-db-column.
 * Added sort order as json response property.
 * Added endpoint to list all fertilizer prices.
 * Added migration for sort column and corresponding query.

`1.1.2` 20/04/2020 15:36
----
 * Merge pull request #78 from masgeek/masgeek-patch-2.
 * Update FertilizerPriceService.kt.
 * Merge branch 'develop' into masgeek-patch-2.
 * Merge pull request #77 from masgeek/masgeek-patch-1.
 * Update FertilizerPriceService.kt.
 * Update FertilizerPriceDto.kt.

`1.1.1` 20/04/2020 14:51
----
 * Merge pull request #75 from masgeek/fix/sorting-index.
 * Updates to sorting.
 * Merge pull request #74 from masgeek/master.
 * Merge pull request #73 from masgeek/develop.

`1.1.0` 16/04/2020 21:09
----
 * Merge pull request #72 from masgeek/feature/indexer-for-caprice.
 * Added indexing id.
 * Merge pull request #70 from masgeek/develop.

`1.0.0` 16/04/2020 19:52
----
 * Merge pull request #71 from masgeek/fix/invalid-docker-group.
 * Reverted to valid group name.
 * Merge pull request #69 from masgeek/feature/adde-exact-cass-price.
 * Merge pull request #67 from masgeek/develop.
 * Add3ed timzone changer to docker file.
 * Added new sord order to be ASC, added new entry for negative price.

`0.9.0` 16/04/2020 17:35
----
 * Merge pull request #68 from masgeek/fix/avg-price-property.
 * Chnaged json property name for average price.
 * Merge pull request #66 from masgeek/feature/cassava-prices-endpoint.
 * Updated fertilizer endpoint.
 * Refactored function and variable names.
 * Added cassavaprices repositoru and support classes and interfaces.
 * Added casava price entity class.
 * Added migration files.
 * Merge pull request #65 from masgeek/develop.

`0.8.0` 28/03/2020 00:59
----
 * Merge pull request #64 from masgeek/fix/conflicts.
 * Merge branch 'develop' into fix/conflicts.
 * Updates.
 * Added automatic pull request review.
 * Fix/conflicts (#63).
 * #### Fixed conflicts in:.
 * Fix/formatted json saving (#61).
 * Release 0.7.1 (#60).

`0.7.1` 26/03/2020 14:57
----
 * Added seeder sql script (#59).
 * Added seeder sql script.
 * Release 0.7.0 (#58).

`0.7.0` 16/03/2020 13:42
----
 * Feature/json saving (#57).
 * Release 0.6.2 (#56).
 * Merge branch 'master' into develop.

`0.6.2` 13/03/2020 08:06
----
 * Fixed message validation to prevent double sending of messages and to also ensure no message is sent when there is no recommendations (#55).
 * Release 0.6.1 (#54).
 * Merge branch 'master' into develop.

`0.6.1` 10/03/2020 11:25
----
 * Fixed message validation to prevent double sending of messages and to also ensure no message is sent when there is no recommendations (#53).
 * Added better check to not send sms when to fertilizer recommendations are available.
 * Added checker for when there is no recommendation SMS should not be sent.
 * Release 0.6.0 (#52).
 * Merge branch 'master' into develop.

`0.6.0` 09/03/2020 09:19
----
 * Added fix for phne number to pick correct value for sms sending (#51).
 * Release 0.5.1 (#50).
 * Merge branch 'master' into develop.

`0.5.1` 09/03/2020 07:36
----
 * Feature/fertilizer filters (#49).
 * Release 0.5.0 (#47).

`0.5.0` 02/03/2020 08:27
----
 * Feature/operations cost (#48).
 * Merge branch 'master' into develop.
 * Featue/cis cim fertilizer availability (#46).
 * Release 0.4.1 (#45).
 * Merge branch 'master' into develop.

`0.4.1` 19/02/2020 13:32
----
 * Fix/docker image rename (#44).
 * Release 0.4.0 (#42) (#43).
 * Merge branch 'develop' into master.
 * Release 0.4.0 (#42).
 * Merge branch 'master' into develop.

`0.4.0` 19/02/2020 13:11
----
 * Feature/use case headers (#41).
 * Merge remote-tracking branch 'origin/develop' into develop.
 * Update README.md.
 * Merge remote-tracking branch 'origin/develop' into develop.
 * Release 0.3.0 (#39).
 * Merge branch 'master' into develop.
 * Merge remote-tracking branch 'origin/develop' into develop.

`0.3.0` 02/12/2019 11:54
----
 * Feature/codebeat markdown (#38).
 * Release 0.2.0 (#35).

`0.2.0` 28/11/2019 16:34
----
 * Feature/codebeat markdown (#37).
 * Merge branch 'master' into develop.
 * Merge remote-tracking branch 'origin/develop' into develop.
 * Fix/plumber response (#36).
 * Merge remote-tracking branch 'origin/develop' into develop.
 * Feature/compute payload (#34).
 * Release 0.1.2 (#32).
 * Merge remote-tracking branch 'origin/develop' into develop.
 * Merge branch 'master' into develop.

`0.1.2` 15/11/2019 10:07
----
 * Fix/cassava unit weight (#33).
 * Merge remote-tracking branch 'origin/develop' into develop.
 * fix/cassava produce type (#31).
 * Release 0.1.1 (#29).
 * Merge branch 'master' into develop.
 * Merge branch 'develop' of github.com:masgeek/akilimo-api into develop.

`0.1.1` 12/11/2019 11:52
----
 * Deleted orphaned classes (#30).
 * Merge remote-tracking branch 'origin/develop' into develop.
 * Deleted orphaned classes.
 * fix/duplicated config (#28).
 * Release 0.1.0 (#24).
 * Merge branch 'master' into develop.
 * # Fixed Conflicts: (#27).

`0.1.0` 12/11/2019 10:04
----
 * Removed hardcoded coordinate values (#25).
 * [ImgBot] Optimize images (#23).
 * Feature/added demo endpoints (#22).
 * Feature/added demo endpoints (#21).
 * Added skipped return statement (#19).
 * Added codebeatsettins file (#18).
 * removed mergify do not merge rule (#17).
 * Fixed payload properties naming issue #15 (#16).
 * Feature/starch factories (#1).
 * Feature/fertilizer ordering column (#13).
 * Feature/starch factories (#10).
 * Fix/null pointers (#9).
 * Fix/test me (#8).
 * Master (#7).
 * # Conflicts: (#6).
 * Merge branch 'feature/jenkins-file' into develop.
 * Fine grained releases.
 * Feature/jenkins file (#3).
 * Fine grained releases.
 * Stages correction.
 * Corrected conditional building for branch names.
 * Resolves issue #2.
 * Reordered steps.
 * Updated readme.
 * Updated readme.
 * Testing of docker credentials.
 * dockerhub.
 * dockerhub.
 * dockerhub.
 * dockerhub.
 * dockerhub.
 * dockerhub.
 * Test.
 * Test.
 * Test.
 * Test.
 * Test.
 * Test.
 * Test.
 * Set major version to be fixed.
 * Added more build signatures.
 * Removed environment variables entry.
 * Tested environment variables.
 * Testing.
 * Added steps to artifact archiving.
 * Corrected environment variables.
 * Added artifact archiving step.
 * Chmod gradlew permissions.
 * Chmod gradlew permissions.
 * Chmod gradlew permissions.
 * Conditional correction.
 * Steps correction.
 * Corrected bad syntax in pipeline script.
 * Corrected bad syntax in pipeline script.
 * Corrected bad syntax in pipeline script.
 * Corrected bad syntax in pipeline script.
 * Corrected bad syntax in pipeline script.
 * Testing branch conditional.
 * Testing branch conditional.
 * Testing branch conditional.
 * Testing branch conditional.
 * Testing branch conditional.
 * Testing branch conditional.
 * Testing branch conditional.
 * Testing branch conditional.
 * Gradlew script addition.
 * Streams editing.
 * Streams editing.
 * Removed docker process.
 * Added ocker build stage.
 * Added ocker build stage.
 * Added ocker build stage.
 * Test triggers.
 * Test triggers.
 * Added jenkins file.
 * Merged in fix/version-number (pull request #28).
 * Merged in feature/ferilizer-list (pull request #27).
 * Merged in fix/fertilizer-custom-names (pull request #25).
 * Merged in feature/custom-fertilizer (pull request #23).
 * Merged in fix/env-variables (pull request #22).
 * Merged in feature/plumbr-endpoints (pull request #21).
 * Merged in feature/country-endpoints (pull request #20).
 * Merged in feature/fertilizer-price-per-bag (pull request #19).
 * Merge remote-tracking branch 'origin/develop' into develop.
 * Merged in feature/fertilizer-list (pull request #18).
 * Merged in feature/fertilizer-prices (pull request #17).
 * Added fertilizer components.
 * Merged in feature/logging (pull request #16).
 * Merged in fix/plumber_url_variable_name (pull request #15).
 * Merged in release/version-1 (pull request #14).
 * Merged in feature/fertilizer-processing (pull request #13).
 * Added proper null validation.
 * Adde check for null values.
 * Fixed variable naming.
 * Merged in fix/sms-messaging (pull request #12).
 * Merged in Sammy-M/fertilizerrecommendationscontrollerkt-ed-1560094361651 (pull request #11).
 * Merged in feature/api-examples (pull request #10).
 * Merged in feature/api-examples (pull request #8).
 * Added infobip certificate to keystore.
 * Fixed variable namings.
 * Merged in feature/messaging (pull request #7).
 * Added sample properties for localDate.
 * Merged in feature/payload (pull request #5).
 * Fixed payload mapping.
 * Update to return style.
 * Added more logs.
 * REverted to circle ci.
 * Updated endpoints for dev environment.
 * Added mising package files.
 * Disabled circleci temporarily.
 * Added user add command.
 * Added user add command.
 * Added user add command.
 * Added user add command.
 * Added user add command.
 * Added new add user commands.
 * corrected docker namespace.
 * Added circeci config.
 * Removed files.
 * Removed files.
 * Added keystore file.
 * Added keystore file.
 * Updated to openjdk 11 slim.
 * added proper migrations.
 * Fixed merged conflicts.
 * Updates to json mapping.
 * Updates to json mapping.
 * Updated endpoints.
 * Updated docker compose file.
 * fixed jvm target.
 * Updates.
 * Updates.
 * Updates.
 * Updates.
 * Added html template.
 * Updated readme badges.
 * Added pipeline badge.
 * Updated sample docker compose.
 * Merged in develop (pull request #3).
 * Merged in feature/database-mapping (pull request #2).
 * Added docker configurations.
 * Added docker configurations.
 * Updates on logging.
 * Improved logging.
 * Added null constraints.
 * Updates on dto mapping.
 * Refactored class names.
 * Refactored class names.
 * Added gitignore.
 * Updates to mapping.
 * Added first skeleton.
 * Initial commit.
