pipeline {
    agent any
    stages {
        stage('Three') {
            when {
                not {
                    branch "master"
                }
            }
            steps {
                echo "Hello not master"
            }
        }

        stage('Test feature branches'){
            when {
                expression { BRANCH_NAME ==~ /(feature|staging)/ }
            }
            steps{
                echo "Hello universe try me"
                sh './gradlew clean test --no-daemon' //run a gradle task
            }
        }


    }
}