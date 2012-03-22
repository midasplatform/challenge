add_subdirectory( base )

#Style
add_midas_style_test( StyleChallengeModelsBase ${CMAKE_SOURCE_DIR}/modules/challenge/models/base/ )
add_midas_style_test( StyleChallengeModelsPdo ${CMAKE_SOURCE_DIR}/modules/challenge/models/pdo/ )
add_midas_style_test( StyleChallengeModelsDao ${CMAKE_SOURCE_DIR}/modules/challenge/models/dao/ )
