# 0.3.3
- Fix stability in module.xml

# 0.3.2
- Missing domain name for some translations ([#7](https://github.com/thelia-modules/BackOfficePath/pull/7))

# 0.3.1
- Really Fix: All case of wrong responses with old and new path
  + All those [#1](https://github.com/thelia-modules/BackOfficePath/issues/1)
  + All REQUEST_URI beginning by <DEFAULT_PATH> (`admin`) : `monsite.com/<DEFAULT_PATH>/modules`,  `monsite.com/<DEFAULT_PATH>/configuration`, ... 

# 0.3.0
- Fix: set request context before throwing `NotFoundHttpException`

# 0.2.0
- Fix redirection on base admin path to login and home, fix regex to take care about base prefix path ([#4](https://github.com/thelia-modules/BackOfficePath/pull/4))
- Remove unused use statement ([#3](https://github.com/thelia-modules/BackOfficePath/pull/3))
- Minor fixes ([#2](https://github.com/thelia-modules/BackOfficePath/pull/2))

# 0.1.0
- Initial release.