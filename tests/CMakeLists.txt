add_subdirectory( controllers )
add_subdirectory( models )

add_midas_style_test( StyleTestsChallengeControllers ${CMAKE_SOURCE_DIR}/modules/challenge/tests/controllers )
add_midas_style_test( StyleTestsChallengeModels ${CMAKE_SOURCE_DIR}/modules/challenge/tests/models )
