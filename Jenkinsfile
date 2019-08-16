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
        stage('Clean code base') {
            steps {
                echo "Cleaning project"
                sh './gradlew clean'
            }
        }

        stage('Run tests and check coverage') {
            steps {
                echo "Running tests"
                sh './gradlew check'
            }
        }
        stage('Build binary files for release branches') {
            steps {
                when {
                    not { branch 'master'; branch 'develop' }
                }
                echo "Running tests"
                sh './gradlew build assemble'
            }
        }
        stage('Build latest') {
            when {
                not { branch 'master'; branch 'develop' }
            }
            steps {
                echo "Building docker image"
                sh "docker build -f Dockerfile -t iita/acai-akilimo-api:latest ."
            }
        }

    }
}