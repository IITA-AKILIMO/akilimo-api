pipeline {
    agent any

    environment {
        registry = 'iita/acai-akilimo-api'
        registryCredential = 'dockerhub'
        dockerImage = ''
    }
    stages {

        stage('Chmod permissions') {
            steps {
                echo "Chmod permissions"
                sh 'chmod +x ./gradlew'
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
            when {
                anyOf {
                    branch 'master';branch 'develop'
                }

            }
            steps {
                echo "Running tests"
                sh './gradlew build assemble'
            }
        }

        stage('Build latest') {
            when {
                anyOf {
                    branch 'master'; branch 'develop'
                }
            }
            steps {
                echo "Building docker image"
                script {
                    dockerImage = docker.build registry + ":${BUILD_NUMBER}"
                }
            }
        }


        stage('Push latest image') {
            when {
                branch "develop"
            }
            steps {
                script {
                    docker.withRegistry('', registryCredential) {
                        dockerImage.push('latest')
                    }
                }
                echo "Create git release"
                sh "git tag ${BUILD_TAG}"

            }
        }


        stage('Push production image') {
            when {
                branch "master"
            }
            steps {
                script {
                    docker.withRegistry('', registryCredential) {
                        dockerImage.push('production')
                    }
                }
            }
        }

        stage('Remove Unused docker image') {
            when {
                anyOf {
                    branch "master";
                    branch "develop"
                }
            }
            steps {
                sh "docker rmi $registry:$BUILD_NUMBER"
            }
        }

        stage('Tag beta release') {
            when {
                branch "develop"
            }
            steps {
                echo "Create git release"
                sh "git tag beta-${BUILD_TAG}"
                echo "Push git release"
                sh "git push origin --tags"

            }
        }

        stage('Tag production release') {
            when {
                branch "master"
            }
            steps {
                echo "Create git release"
                sh "git tag release-${BUILD_TAG}"
                echo "Push git release"
                sh "git push origin --tags"

            }
        }

        stage('Archive artifacts') {
            when {
                anyOf {
                    branch "master";
                    branch "develop"
                }
            }
            steps {
                archiveArtifacts artifacts: 'build/libs/akilimo*.jar'
            }
        }
    }
}